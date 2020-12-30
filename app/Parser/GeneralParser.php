<?php

namespace App\Parser;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class GeneralParser
{
    private $included;
    private $relation;
    private $nestedrelation;
    private $data;
    private $link;
    private $meta;
    private $result;
    private $app;
    public $customFilter = "";
    public $module       = "";

    public function __construct()
    {
        $this->included = array();
        $this->relation = array();
        $this->data     = array();
        $this->link     = array();
        $this->meta     = array();
        $this->result   = array();
    }

    public function normalize($data = array())
    {
        if (array_key_exists('meta', $data)) {
            $this->meta = $data['meta'];
        }
        if (array_key_exists('links', $data)) {
            $this->link = $data['links'];
        }

        if ($data) {
            if ($data['data']) {
                if (array_key_exists('included', $data)) {
                    $this->included = $data['included'];
                    $this->normalizeIncluded();
                }

                //unset($data['data']['type']);
                $this->data = $data['data'];

                $this->parseData();
                $this->applyCustomFilter();
            } else {
                $this->data = $data['data'];
            }
            $this->generateResult();
        }

        return $this->result;
    }

    private function realParseData($data, $type)
    {
        $tmp  = array();
        $attr = array();

        switch ($type) {
            case 'policy':
                if (empty($data['attributes']['data'])) {
                    break;
                }

                $result     = [];
                $population = [];
                $_data      = json_decode($data['attributes']['data']);

                if (isset($_data->lookup_url)) {
                    $dispatcher = app('Dingo\Api\Dispatcher');
                    $api        = '/api/v1/' . $_data->lookup_url;
                    $api        = str_replace("//", "/", $api);
                    $_response  = $dispatcher->get($api);

                    if ($_response['success']) {
                        $_population = [];
                        foreach ($_response['data']['data'] as $record) {
                            $_population[] = ['id' => isset($record['attributes']['real_id']) ? $record['attributes']['real_id'] : $record['id'], 'title' => $record['attributes']['title']];
                        }
                        $population = $_population;
                    }
                    $population = $_population;
                } elseif (isset($_data->lookup_values)) {
                    $population  = json_decode(json_encode($_data->lookup_values), true);
                    $_population = [];
                    foreach ($population as $value => $label) {
                        $_population[] = ['id' => $value, 'title' => $label];
                    }
                    $population = $_population;
                }

                $result                     = [
                    'dropdown_data'        => $population,
                    'dropdown_type'        => $data['attributes']['datatype'] == 'array' ? 'multiple' : 'single',
                    'dropdown_placeholder' => isset($_data->lookup_label) ? $_data->lookup_label : null
                ];
                
                if (in_array($data['attributes']['datatype'], ['array', 'array-object']) && empty($_data->lookup_url) && empty($_data->lookup_values)) {
                    $result['type'] = isset($_data->type) ? $_data->type : null;
                    $result['array_min'] = isset($_data->array_min) ? $_data->array_min : null;
                    $result['array_max'] = isset($_data->array_max) ? $_data->array_max : null;
                    $result['array_fields'] = isset($_data->array_fields) ? json_decode(json_encode($_data->array_fields), true) : null;
                }
                
                $data['attributes']['data'] = $result;
                break;
            case 'booking':
                $data['attributes']['data']     = $this->normalizeJsonFormat($data['attributes']['data']);
                $data['attributes']['facility'] = array("Breakfast", "Dinner", "Spa", "Swimming Pool", "Pijat Plus Plus");
                break;
        }

        $attr = array_merge($data, $data['attributes']);
        unset($attr['attributes']);

        if (array_key_exists('relationships', $data)) {
            $this->relation = $data['relationships'];
            $this->parseRelation();
            $attr = array_merge($attr, $this->relation);
            unset($attr['relationships']);
        }

        return $attr;
    }

    public function parseData($type = null)
    {
        $tmp  = array();
        $attr = array();

        if ($this->data) {
            if (array_key_exists('id', $this->data)) {
                $tmp = $this->realParseData($this->data, $this->data['type']);
            } else {
                foreach ($this->data as $parser) {
                    $tmp[] = $this->realParseData($parser, $parser['type']);
                }
            }
        }

        $this->data = $tmp;
        return;
//
//        if($this->data) {
//          if(array_key_exists('id', $this->data)) {
//              if($this->data['type'] == 'booking') { $this->data['attributes']['data'] = $this->normalizeJsonFormat($this->data['attributes']['data']); $this->data['attributes']['facility'] = array("Breakfast", "Dinner", "Spa", "Swimming Pool", "Pijat Plus Plus");  }
//              $attr = array_merge($this->data, $this->data['attributes']);
//
//              if(array_key_exists('relationships', $this->data)) {
//                  $this->relation = $this->data['relationships'];
//                  $this->parseRelation();
//                  $attr = array_merge($attr, $this->relation);
//                  unset($attr['relationships']);
//              }
//
//              unset($attr['attributes']);
//              $tmp = $attr;
//          }
//          else {
//              foreach($this->data as $parser) {
//                $result = $this->realParseData($this->data, $parser['type']);
//                if($parser['type'] == 'booking') { $parser['attributes']['data'] = $this->normalizeJsonFormat($parser['attributes']['data']); $parser['attributes']['facility'] = array("Breakfast", "Dinner", "Spa", "Swimming Pool", "Pijat Plus Plus"); }
//                  unset($parser['type']);
//                  $attr = array_merge($parser, $parser['attributes']);
//
//                  if(array_key_exists('relationships', $parser)) {
//                      $this->relation = $parser['relationships'];
//                      $this->parseRelation();
//                      $attr = array_merge($attr, $this->relation);
//                      unset($attr['relationships']);
//                  }
//
//                  unset($attr['attributes']);
//
//
//                  $tmp[] = $attr;
//              }
//          }
//        }
//
//        $this->data = $tmp;
    }

    public function parseRelation()
    {
        $tmp      = array();
        $rel      = array();
        $multirel = array();
        $nested   = array();
        if ($this->relation) {
            foreach ($this->relation as $key => $relation) :
                if (!array_key_exists('id', $relation['data'])) {
                    $rel      = array();
                    $multirel = array();
                    $nested   = array();
                    foreach ($relation['data'] as $arrayrelation) :
                        foreach ($this->included as $included) :
                            if ($arrayrelation['type'] == $included['type'] && $arrayrelation['id'] == $included['id']) {
                                $nested = array_merge($arrayrelation, $included['attributes']);

                                if (array_key_exists('relationships', $included)) {
                                    $var    = $this->parseNestedRelation($included['relationships']);
                                    $nested = array_merge($nested, $var);
                                    unset($nested['relationships']);
                                }

                                $multirel[] = $nested;
                                break;
                            }
                    endforeach;
                    endforeach;
                    $rel = $multirel;
                } else {
                    $rel = $relation['data'];
                    foreach ($this->included as $included) :
                        if ($relation['data']['type'] == $included['type'] && $relation['data']['id'] == $included['id']) {
                            $rel = array_merge($relation['data'], $included['attributes']);

                            if (array_key_exists('relationships', $included)) {
                                $var    = $this->parseNestedRelation($included['relationships']);
                                $nested = array_merge($nested, $var);
                                unset($nested['relationships']);
                            }

                            $rel = array_merge($rel, $nested);
                            break;
                        }
                    endforeach;
                }

            $tmp[$key] = $rel;

            endforeach;

            $this->relation = $tmp;
        }
    }

    public function parseNestedRelation($var)
    {
        $tmp      = array();
        $rel      = array();
        $multirel = array();
        $nested   = array();

        if ($var) {
            foreach ($var as $key => $relation) :
                if (!array_key_exists('id', $relation['data'])) {
                    $rel      = array();
                    $multirel = array();
                    $nested   = array();

                    foreach ($relation['data'] as $arrayrelation) :
                        foreach ($this->included as $included) :
                            if ($arrayrelation['type'] == $included['type'] && $arrayrelation['id'] == $included['id']) {
                                $nested = array_merge($arrayrelation, $included['attributes']);

                                if (array_key_exists('relationships', $included)) {
                                    $var    = $this->parseNestedRelation($included['relationships']);
                                    $nested = array_merge($nested, $var);
                                    unset($nested['relationships']);
                                }

                                $multirel[] = $nested;
                                break;
                            }
                    endforeach;
                    endforeach;

                    $result = $multirel;
                } else {
                    $rel = $relation['data'];
                    foreach ($this->included as $included) :
                        if ($relation['data']['type'] == $included['type'] && $relation['data']['id'] == $included['id']) {
                            $rel = array_merge($relation['data'], $included['attributes']);

                            if (array_key_exists('relationships', $included)) {
                                $var    = $this->parseNestedRelation($included['relationships']);
                                $nested = array_merge($nested, $var);
                                unset($nested['relationships']);
                            }

                            $rel = array_merge($rel, $nested);
                            break;
                        }
                    endforeach;

                    $result = $rel;
                }

            $tmp[$key] = $result;
            endforeach;
        }

        return $tmp;
    }

    public function normalizeIncluded()
    {
        $tmp = array();
        foreach ($this->included as $included) :
            if ($included['type'] == 'booking') {
                $included['attributes']['data']       = $this->normalizeJsonFormat($included['attributes']['data']);
                $this->data['attributes']['facility'] = array("Breakfast", "Dinner", "Spa", "Swimming Pool", "Pijat Plus Plus");
            }

        if ($included['type'] == 'invoice') {
            if (array_key_exists('from_data', $included['attributes'])) {
                $included['attributes']['from_data'] = $this->normalizeJsonFormat($included['attributes']['from_data']);
            }

            if (array_key_exists('to_data', $included['attributes'])) {
                $included['attributes']['to_data'] = $this->normalizeJsonFormat($included['attributes']['to_data']);
            }
        }

        $tmp[] = $included;
        endforeach;
        $this->included = $tmp;
    }

    public function normalizeJsonFormat($data)
    {
        return json_decode($data);
    }

    public function generateResult()
    {
        $result = array(
            'data' => $this->data,
            'meta' => $this->meta,
            'link' => $this->link
        );

        $this->result = $result;
    }

    public function applyCustomFilter()
    {
        // available module : Trip['group booking']

        switch ($this->customFilter) {
            case "tripgroupbooking":
                $this->groupBookingByTripModule();
                break;
        }
    }

    public function groupBookingByTripModule()
    {
        $tmp = array();
        if ($this->module == "trip") {
            foreach ($this->data as $data) :
                $hotels = array();
            $flight = array();

            foreach ($data['djurneeBooking'] as $djurneeBooking) :
                    if ($djurneeBooking['booking']['source']['title'] == "Flight - Vendor" || $djurneeBooking['booking']['source']['title'] == "Flight - Tiket.com API v.2") {
                        $flight[] = $djurneeBooking;
                    } elseif ($djurneeBooking['booking']['source']['title'] == "Hotel - Vendor") {
                        $hotels[] = $djurneeBooking;
                    }
            endforeach;

            $data['booking_flights'] = $flight;
            $data['booking_hotels']  = $hotels;

            unset($data['djurneeBooking']);

            $tmp[] = $data;
            endforeach;

            $this->data = $tmp;
        }
    }

    public function normalizeV2($allData = array())
    {
        if (array_key_exists('meta', $allData)) {
            $this->meta = $allData['meta'];
        }
        if (array_key_exists('links', $allData)) {
            $this->link = $allData['links'];
        }
        $result = [];

        $data     = @$allData['data'];
        $included = @$allData['included'];

        if (is_array(@$data[0])) {
            foreach ($data as $value) {
                $this->data[] = $this->parseFirstData($value, $included);
            }
        } else {
            $this->data = $this->parseFirstData($data, $included);
        }

        $this->generateResult();

        return $this->result;
    }

    private function parseFirstData($data, $included)
    {
        $result = [];

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'relationships') {
                    foreach ($value as $key2 => $value2) {
                        $result[$key2] = $this->getFromInclude($value2['data'], $included);
                    }
                } elseif ($key == 'attributes') {
                    foreach ($value as $key2 => $value2) {
                        $result[$key2] = $value2;
                    }
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function getFromInclude($data, $included)
    {
        if (is_array(@$data[0])) {
            $result = [];
            foreach ($data as $value) {
                $result[] = $this->getFromInclude($value, $included);
            }
            return $result;
        } else {
            return $this->searchInclude($data, $included);
        }
    }

    private function searchInclude($value, $included)
    {
        $result = $value;

        $attribute = [];

        foreach ($included as $include) {
            if ($include['type'] == @$value['type'] && $include['id'] == @$value['id']) {
                $attribute = $include;
                break;
            }
        }

        foreach ($attribute as $key => $value) {
            if ($key == 'relationships') {
                foreach ($value as $key2 => $value2) {
                    $result[$key2] = $this->getFromInclude($value2['data'], $included);
                }
            } elseif ($key == 'attributes') {
                foreach ($value as $key2 => $value2) {
                    $result[$key2] = $value2;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
