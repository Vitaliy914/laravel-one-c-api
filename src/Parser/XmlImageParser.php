<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;

use Illuminate\Database\Eloquent\Model;

class XmlImageParser implements XmlImageParserInterface
{

    use XmlModel;

    public function __construct()
    {
        $this->initModel('images');
    }

    public function run(array $images, Model $model) : void
    {
        $prod = $this->model::where('product_id', $model->id)->get()->pluck('name')->toArray();
        foreach ($images as $variant) {
            $newProd[] = $variant;
            if(!in_array($variant,$prod)) {
                $item = new $this->model();
                $item->product_id = $model->id;
                $item->name = $variant;
                $item->save();
            }
        }
        $unlink = array_diff($prod,$newProd);
        if(!empty($unlink)){
            $directory = config('one-c.setup.app_path')."storage/app/public/images/";
            foreach ($unlink as $file){
                $this->model::where('product_id', $model->id)->where('name',$file)->delete();
                if(file_exists($directory.$file)) {
                    unlink($directory . $file);
                }
            }
        }
    }
}
