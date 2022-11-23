<?php
namespace Vitaliy914\OneCApi\Controllers;

use Illuminate\Routing\Controller;
use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;
use Vitaliy914\OneCApi\Response;
use Vitaliy914\OneCApi\Services\CatalogService;
use Illuminate\Http\Request;

class OneCApiController extends Controller
{
    private $catalogService;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->get('type');

        try {
            if ($type == 'catalog') {
                $result = $this->runCatalogService($request);
                return response($result, 200, ['Content-Type', 'text/plain']);
            }
            else
                throw new ExceptionOneCApi('OneCApi: type='.$type." not supported.");
        }
        catch (ExceptionOneCApi $e)
        {
            \Log::error("OneCApi: failure \n".$e->getMessage()."\n".$e->getFile()."\n".$e->getLine()."\n");

            $response = new Response();

            $response->failure()
                ->set($e->getMessage())
                ->set($e->getFile())
                ->set($e->getLine());

            return response($response->getResponse(), 500, ['Content-Type', 'text/plain']);
        }
    }

    /**
     * @param Request $request
     * @return string
     * @throws ExceptionOneCApi
     */
    private function runCatalogService(Request $request)
    {
        if(!$this->catalogService)
            $this->catalogService = new CatalogService($request);

        return $this->catalogService->route();
    }
}
