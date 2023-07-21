<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Vitaliy914\OneCApi\Auth;
use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;
use Vitaliy914\OneCApi\Models\OnecapiGroup;
use Vitaliy914\OneCApi\Parser\XmlCatalogParser;
use Vitaliy914\OneCApi\Parser\XmlOffersParser;
use Vitaliy914\OneCApi\Response;
use Vitaliy914\OneCApi\Helpers\StringHelper;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Cache;

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
    public function route(): string
    {
        $mode = $this->request->get('mode');

        switch ($mode) {
            case 'checkauth':
                return $this->checkauth();
            case 'init':
                return $this->init();
            case 'file':
                return $this->file();
            case 'import':
                return $this->import();
        }

        throw new ExceptionOneCApi('OneCApi: CatalogService->route error, mode=' . $mode . " not supported.");
    }


    /**
     * ?type=catalog&mode=checkauth
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function checkauth(): string
    {
        return $this->getAuth()->auth();
    }

    /**
     * ?type=catalog&mode=init
     *
     * @return string
     * @throws ExceptionOneCApi
     */
    public function init(): string
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
    public function file(): string
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
    public function import(): string
    {
        $this->getAuth()->isAuth();

        $response = new Response();

        if ($this->config['use_zip'])
            $this->getFileService()->unzipAll();

        $fileName = $this->request->get('filename');

        switch ($fileName) {
            case 'import.xml':
                $catalogParser = new XmlCatalogParser();
                $catalogParser->init($fileName)->runCatalog();
                break;
            case 'offers.xml':
                Db::statement('update onecapi_products_in_shops set count = 0 where true;');
                $offersParse = new XmlOffersParser();
                $offersParse->init($fileName)->run();
                $this->createMagic();
                break;
        }
        $response->success($this->request->getSession()->getId());

        return $response->getResponse();
    }

    /**
     * @return Auth
     * @throws ExceptionOneCApi
     */
    protected function getAuth(): Auth
    {
        if ($this->auth)
            return $this->auth;
        else {
            if ($this->request) {
                $this->auth = new Auth($this->request);
                return $this->auth;
            } else {
                throw new ExceptionOneCApi('OneCApi: CatalogService->getAuth error, no request.');
            }
        }
    }

    /**
     * @return FileService
     */
    protected function getFileService(): FileService
    {
        if ($this->fileService)
            return $this->fileService;
        else {
            $this->fileService = new FileService();
            return $this->fileService;
        }
    }

    private function createMagic()
    {
        $catalogs = OnecapiGroup::where('slug', '=', '')->orWhereNull('slug')->get();
        if ($catalogs->count() > 0) {
            foreach ($catalogs as $c) {
                $c->slug = StringHelper::translitUrl($c->name);
                $c->save();
            }
        }
        Db::statement('drop table products;');
        Db::statement("delete  FROM `onecapi_property_values` WHERE `property_variant_sku` = 'false' or  `property_variant_sku` = '';");
        Db::statement("create table products as
                                select pp.sku AS property_sku,g.sku AS group_sku,
                                    g.parent_sku AS parent_sku,p.sku AS product_sku, pr.unit, p.description,
                                    g.name AS gname,p.name AS name, p.art AS art,p.barcode AS barcode,
                                    pp.name AS property_name,REPLACE(i.name,'import_files','images') AS image,
                                    m.slug, concat(g.id,'-',g.slug) as category, pr.price_per_unit, pr.currency,
                                       pr.price_with_discount, pr.discount,
                                    p.created_at, p.updated_at
                                from onecapi_products p
                                join onecapi_groups g on g.sku = p.group_sku
                                join onecapi_property_values ppv on p.sku = ppv.product_sku
                                join onecapi_properties pp on pp.sku = ppv.property_sku
                                join onecapi_prices pr on pr.product_sku = p.sku
                                join menus m on m.sku = pp.sku
                                left join onecapi_images i on p.id = i.product_id
                                where  (ppv.property_variant_sku = 'true')");
        Db::statement('ALTER TABLE products CHANGE price_with_discount price_with_discount float NULL AFTER currency;');
        Db::statement('CREATE INDEX product_sku ON products(product_sku);');
        Db::statement('CREATE INDEX slug ON products(slug);');
        Db::statement('CREATE INDEX category ON products(category);');
        Db::statement('CREATE INDEX price_per_unit ON products(price_per_unit);');
        Db::statement('CREATE INDEX created_at ON products(created_at);');
        Db::statement('CREATE INDEX product_name ON products(name);');
        Db::statement('CREATE INDEX group_sku ON products(group_sku);');
        Db::statement('CREATE INDEX slug_category ON products(slug,category);');
        Db::statement('CREATE INDEX parent_sku ON products(parent_sku);');
        Db::statement('CREATE INDEX property_sku ON products(property_sku);');

        Db::statement('Drop view attributes;');
        Db::statement("create view attributes as 
                            select pvv.id AS id,pv.product_sku AS product_sku,pvv.name AS value,p.name AS atribut 
                            from onecapi_property_variants pvv 
                            join onecapi_property_values pv on pv.property_variant_sku = pvv.property_sku
                            join onecapi_properties p on p.sku = pvv.sku
                            where p.name = 'Производитель_';
                        ");
        Db::statement('Drop table full_menus;');

        Db::statement("create table full_menus as
                                with recursive tree (tree_slug,name, sku, parent_sku, level, g_id )
                                as (select concat(id,'-',slug), name, sku,parent_sku, 0, id
                                   from onecapi_groups
                                   where parent_sku is null or parent_sku = ''
                                union all
                                   select concat(id,'-',slug), onecapi_groups.name, onecapi_groups.sku, onecapi_groups.parent_sku, tree.level + 1, id
                                   from onecapi_groups
                                     inner join tree on tree.sku= onecapi_groups.parent_sku)
                                select m.slug, m.sku, m.name, m.image, m.order, tree_slug, tree.name as tree_name, tree.sku as tree_sku,
                                        tree.parent_sku, tree.level, g_id
                                from tree
                                join menus m on true 
                                group by  m.slug, m.sku, m.name, m.image, m.order, tree.name, tree.sku, tree.parent_sku, tree.level,tree_slug, g_id;");
        Db::statement('ALTER TABLE full_menus ADD id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY;');
        Db::statement('create temporary table menu_not_del as 
                            WITH RECURSIVE cte (name, sku, parent_sku, slug ,level) AS (
                                  SELECT name, sku, parent_sku,p.slug , 0
                                    FROM onecapi_groups o
                                    join (select slug ,group_sku from products group by group_sku , slug , category) as p on o.sku =group_sku
                                  UNION
                                  SELECT t.name,t.sku,  t.parent_sku, cte.slug ,level+1
                                    FROM cte
                                    JOIN onecapi_groups AS t ON t.sku = cte.parent_sku
                                    join (select slug ,group_sku from products group by group_sku , slug , category) as p on p.slug=cte.slug 
                        ), q as ( 
                            SELECT t.sku, t.slug
                              FROM cte t
                            join full_menus m on t.sku=m.tree_sku 
                            group by 1,2
                        ) 
                        select m.id from full_menus m
                        join q on q.slug = m.slug and q.sku=m.tree_sku;');
        Db::statement('delete from full_menus where id not in(select id from menu_not_del );');
        Db::statement("delete from `full_menus` WHERE `tree_name` = 'Аквариумистика' AND `slug` != 'fishes';");
        Db::statement("delete from `full_menus` WHERE `tree_name` = 'Одежда' AND `slug` = 'farm_animals';");
        Db::statement("delete from `full_menus` WHERE `tree_name` = 'Премиксы для сельскохозяйственных животных и птиц' AND (`slug` = 'cats' OR `slug` = 'dogs');");
        Cache::flush();
        $directory = config('one-c.setup.app_path');
        $c = shell_exec('./copy.sh');
        return $c ?? 'qq';
    }
}
