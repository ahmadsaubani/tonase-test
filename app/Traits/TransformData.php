<?php

namespace App\Traits;

use App\Serializers\TransformerJsonApiSerializer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

trait TransformData
{
    private function getFractalManager()
    {
        $request = app(Request::class);
        $manager = new Manager();
        $manager->setSerializer(new TransformerJsonApiSerializer());
        if (!empty($request->query('include'))) {
            $manager->parseIncludes($request->query('include'));
        }
        return $manager;
    }

    public function item($data, $transformer, $include = null)
    {
        $manager = $this->getFractalManager();
        $resource = new Item($data, $transformer, $transformer->type);
        // dd($resource);
        if ($include) {
            $manager->parseIncludes($include);
        }

        return $manager->createData($resource)->toArray();
    }

    public function collection($data, $transformer, $include = null)
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer, $transformer->type);
        if ($include) {
            $manager->parseIncludes($include);
        }
        return $manager->createData($resource)->toArray();
    }

    /**
     * @param $data
     * @param $transformer
     * @param null $include
     * @return array
     */
    public function paginate($data, $transformer, $include = null)
    {
        $manager = $this->getFractalManager();
        $resource = new Collection($data, $transformer, $transformer->type);
        $resource->setPaginator(new IlluminatePaginatorAdapter($data));
        if ($include) {
            $manager->parseIncludes($include);
        }
        return $manager->createData($resource)->toArray();
    }
}
