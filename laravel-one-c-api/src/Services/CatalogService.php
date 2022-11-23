<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Vitaliy914\OneCApi\Auth;
use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;
use Vitaliy914\OneCApi\Parser\XmlCatalogParser;
use Vitaliy914\OneCApi\Parser\XmlOffersParser;
use Vitaliy914\OneCApi\Response;
use Symfony\Component\HttpFoundation\Request;

class CatalogService
{
    private $auth;

    private $fileService;

    protected $config;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->config = Config::get('one-c.setup');
    }

    /**
     * @return string
     * @throws ExceptionOneCApi
     */
    public function route() : string
    {
        $mode = $this->request->get('mode');

        switch ($mode){
            case 'checkauth':
                return $this->checkauth();
            case 'init':
                return $this->init();
            case 'file':
                return $this->file();
            case 'import':
                return $this->import();
        }

        throw new ExceptionOneCApi('OneCApi: CatalogService->route error, mode='.$mode." not supported.");
    }

    /**
     * ?type=catalog&mode=checkauth
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function checkauth() : string
    {
        return $this->getAuth()->auth();
    }

    /**
     * ?type=catalog&mode=init
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function init() : string
    {
        $this->getAuth()->isAuth();
        $this->getFileService()->unlinkImportDir();

        $response = new Response();
        $response
            ->set('zip', ($this->config['use_zip'] ? 'yes' : 'no'))
            ->set('file_limit', (string)$this->config['file_limit']);

        return $response->getResponse();
    }

    /**
     * ?type=catalog&mode=file
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function file() : string
    {
        $this->getAuth()->isAuth();

        $this->getFileService()->load($this->request);

        $response = new Response();

        return $response->set('success')->getResponse();
    }

    /**
     * ?type=catalog&mode=import&filename=<имя файла>
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function import() : string
    {
        $this->getAuth()->isAuth();

        $response = new Response();

        if($this->config['use_zip'])
            $this->getFileService()->unzipAll();

        $fileName = $this->request->get('filename');

        switch ($fileName){
            case 'import.xml':
                $catalogParser = new XmlCatalogParser();
                $catalogParser->init($fileName)->runCatalog();
                break;
            case 'offers.xml':
                $offersParse = new XmlOffersParser();
                $offersParse->init($fileName)->run();
                break;
        }
        $response->success($this->request->getSession()->getId());
        Db::statement('drop table products;');
        Db::statement("create table products as
                                select pp.sku AS property_sku,g.sku AS group_sku,
                                    g.parent_sku AS parent_sku,p.sku AS product_sku, pr.unit, p.description,
                                    g.name AS gname,p.name AS name, p.art AS art,p.barcode AS barcode,
                                    pp.name AS property_name,REPLACE(i.name,'import_files','images') AS image,
                                    m.slug, concat(g.id,'-',g.slug) as category, pr.price_per_unit, pr.currency,
                                    p.created_at, p.updated_at
                                from onecapi_products p
                                join onecapi_groups g on g.sku = p.group_sku
                                join onecapi_property_values ppv on p.sku = ppv.product_sku
                                join onecapi_properties pp on pp.sku = ppv.property_sku
                                join onecapi_prices pr on pr.product_sku = p.sku
                                join menus m on m.sku = pp.sku
                                left join onecapi_images i on p.id = i.product_id
                                where  (ppv.property_variant_sku = 'true')");
        Db::statement('CREATE INDEX product_sku ON products(product_sku);');
        Db::statement('CREATE INDEX slug ON products(slug);');
        Db::statement('CREATE INDEX category ON products(category);');
        Db::statement('CREATE INDEX price_per_unit ON products(price_per_unit);');
        Db::statement('CREATE INDEX created_at ON products(created_at);');
        Db::statement('CREATE INDEX product_name ON products(`name`);');
        Db::statement('CREATE INDEX group_sku ON products(group_sku);');
        Db::statement('CREATE INDEX slug_category ON products(slug,category);');
        Db::statement('CREATE INDEX parent_sku ON products(parent_sku);');
        Db::statement('CREATE INDEX property_sku ON products(property_sku);');

        Db::statement('Drop table full_menus;');

        Db::statement("create table full_menus as
                                with recursive tree (tree_slug,name, sku, parent_sku, level, g_id )
                                as (select concat(id,'-',slug), name, sku,parent_sku, 0, id
                                   from onecapi_groups
                                   where parent_sku =''
                                union all
                                   select concat(id,'-',slug), onecapi_groups.name, onecapi_groups.sku, onecapi_groups.parent_sku, tree.level + 1, id
                                   from onecapi_groups
                                     inner join tree on tree.sku= onecapi_groups.parent_sku)
                                select m.slug, m.sku, m.name, m.image, m.order, tree_slug, tree.name as tree_name, tree.sku as tree_sku,
                                        tree.parent_sku, tree.level, g_id
                                from tree
                                join products on (tree.sku = products.group_sku or tree.sku = products.parent_sku)
                                join menus m on m.sku = products.property_sku
                                group by  m.slug, m.sku, m.name, m.image, m.order, tree.name, tree.sku, tree.parent_sku, tree.level,tree_slug, g_id;");

        $directory = config('one-c.setup.app_path');
        exec($directory.'php artisan command:CreateSearchIndex');

        return $response->getResponse();
    }

    /**
     * @return Auth
     * @throws ExceptionOneCApi
     */
    protected function getAuth() : Auth
    {
        if($this->auth)
            return $this->auth;
        else{
            if($this->request){
                $this->auth = new Auth($this->request);
                return $this->auth;
            }
            else {
                throw new ExceptionOneCApi('OneCApi: CatalogService->getAuth error, no request.');
            }
        }
    }

    /**
     * @return FileService
     */
    protected function getFileService() : FileService
    {
        if($this->fileService)
            return $this->fileService;
        else{
            $this->fileService = new FileService();
            return $this->fileService;
        }
    }
}
