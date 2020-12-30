<?php

namespace App\Traits;

use App\Parser\GeneralParser;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait ApiResponser
{
    /**
     * @param $message
     * @param int $code
     * @param null $trace
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code = 500, $trace = null, $data = null)
    {
        $httpCode = ($code >= 600 || $code <= 100) ? 500 : $code;

        return response()->json([
            'success' => false,
            'message' => $message,
            'trace'   => $trace,
            'data'    => $data,
            'code'    => $code
        ], $httpCode);
    }

    protected function realErrorResponse(Exception $e)
    {
        $httpCode = ($e->getCode() >= 600 || $e->getCode() <= 100) ? 500 : $e->getCode();

        $filteredTrace = [];

        foreach ($e->getTrace() as $trace) {
            if (strpos(@$trace["class"], "App\\") !== false) {
                $filteredTrace[] = @$trace["class"] . @$trace["type"] . @$trace["function"] . (@$trace["line"] ? ":" . $trace["line"] : null);
            }
        }

        return response()->json([
            'success'         => false,
            'message'         => json_decode($e->getMessage()) ? json_decode($e->getMessage()) : $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'code'            => $e->getCode(),
            'exception_class' => get_class($e),
            'trace'           => $filteredTrace,
        ], $httpCode);
    }

    protected function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function showResult($message, $data, $code = 200)
    {
        if($message instanceof Exception){
            return $this->realErrorResponse($message);
        }
        
        if (is_array($data)) {
            $result = $this->successResponse(array_merge([
                'success' => true,
                'message' => $message,
            ], $data), $code);
        } else {
            $data = $data->toArray();
            $result = $this->successResponse(array_merge([
                'success' => true,
                'message' => $message,
            ], $data), $code);
        }

        return $result;
    }

    protected function showResultV2($message, $data, $code = 200) 
    {
        $generalParser = new GeneralParser();
        $data = $generalParser->normalize($data);
        
        return $this->showResult($message, $data, $code);
    }
}
