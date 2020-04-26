<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;


trait ApiResponser {
    function successResponse($data, $code = 200, $val = '')
    {
        if($data instanceof AnonymousResourceCollection || $val=='') {
            return $data->response()->setStatusCode($code);
        } else {
            return response()->json(['data' => []], $code);
        }
    }

    function errorResponse($message, $code)
    {
        return response()->json(['error' => ['message'=>$message, 'code'=>$code]], $code);
    }

    function showAll($collection, $code = 200)
    {
        if($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code, 'valor');
        }

        if($collection instanceof LengthAwarePaginator) {
            $collection->appends(['per_page' => $this->determinePageSize()]);
        }

        $resource = $collection->first()->resource;

        $transformCollection = $resource::collection($collection);

        return $this->successResponse($transformCollection, $code, 'valor');
    }

    function showOne(Model $instance, $code=200)
    {
        $resource = $instance->resource;

        $transformInstance = new $resource($instance);

        return $this->successResponse($transformInstance, $code);
    }

    function showMessage($message, $code=200)
    {
        return $this->successResponse($message, $code);
    }

    function determinePageSize()
    {
        $rules = [
            'per_page' => 'integer|min:2|max:10000',
        ];

        $perPage = request()->validate($rules);

        return isset($perPage['per_page']) ? (int)$perPage['per_page'] : 25;
    }
}
