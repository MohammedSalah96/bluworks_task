<?php

namespace App\Http\Controllers\Api;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ProblemsController extends ApiController
{
    private $count_rules = [
        'start' => 'required|numeric|lt:end',
        'end' => 'required|numeric',
        'except' => 'required|numeric'
    ];

    private $index_rules = [
        'input_string' => 'required|regex:/^[a-zA-Z]+$/'
    ];

    private $minimum_steps_rules = [
        'n' => 'required',
        'q' => 'required'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    private function gen($start, $end)
    {
        for ($i = $start; $i <= $end; $i++)
            yield $i;
    }

    private function isDigitPresent($num, $digit)
    {
        $num = abs($num);
        while ($num > 0) {
            if ($num % 10 == $digit) {
                return true;
            }
            $num = intval($num / 10);
        }
        return false;
    }

    /**
     *  Make a GET api that have two parameters, start number and
        the end number and should return the count of all numbers
        except numbers with a 5 in it. The start and the end number are
        both inclusive!
     */
    public function getCountExcept(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->count_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }
            $start = $request->start;
            $end = $request->end;
            // if ($request->start < 0 && $request->end < 0) {
            //     $start = abs($request->end);
            //     $end = abs($request->start);
            // }
            $count = 0;
            foreach ($this->gen($start, $end) as $num) {
                if (!$this->isDigitPresent($num, $request->except))
                    $count++;
            }
            return _api_json($count);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }

    private function position($input_string)
    {
        if (strlen($input_string) > 1) {
            $pos = 0;
            $reversed_string = strrev($input_string);
            for ($i = 0; $i < strlen($reversed_string); $i++) {
                $pos += ($this->position($reversed_string[$i]) + ($i != 0 ? 1 : 0)) * pow(26, $i);
            }
            return $pos;
        }
        return ord(strtolower($input_string)) - 97;
    }

    /**
     * Make a GET api that have one parameter named input_string.
        that have the alphabetic string you should return the index of this
        string. index sequence will be like that A=>1, B=>2 ..... , Z=>26,
        AA=>27, AB=>28 ...... , AZ=>52 , BA=>53 , BB=>54 ..... , BZ =>
        78 and so on.
     */
    public function getStringIndex(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->index_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }
            $index = $this->position($request->input_string) + 1;
            return _api_json($index);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }

    private function downToZero($n)
    {
        $table = array_merge([0, 1, 2, 3], array_fill(0, $n - 3, 0));
        for ($i = 4; $i <= $n; $i++) {
            $res = 1 + $table[$i - 1];
            $a = 2;
            while ($a * $a <= $i) {
                if ($i % $a == 0)
                    $res = min($res, 1 + $table[intval($i / $a)]);
                $a += 1;
            }
            $table[$i] = $res;
        }

        return $table[$n];
    }

    /**
     * You are given an array Q of N elements. Each element
        In array Q represent an integer number X.
        The goal is for each element X in the array we need to minimize
        the number of steps required in order to reduce this number to
        zero

        You can perform each step in any of the 2 operations on X in
        each move:
        1: If we take 2 integers a and b where (X == a * b)
        And (a != 1, b != 1) then we can change
        X = max (a, b)
        2: Decrease the value of X by 1.

        Determine the minimum number of moves required to
        reduce the value of X to 0.
     */
    public function getMinmumStepsToZero(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->minimum_steps_rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                return _api_json(new \stdClass(), ['errors' => $errors], 400);
            }
            $size = $request->n;
            $queue = json_decode($request->q);
            if ($size < 1 || $size > pow(10, 4)) {
                $message = _lang('app.size_must_be_between_1_and_10^4');
                return _api_json(new \stdClass(), ['message' => $message], 400);
            }
            if (count($queue) != $size) {
                $message = _lang('app.list_of_elements_must_be_in_size_of') . ' ' . $size;
                return _api_json(new \stdClass(), ['message' => $message], 400);
            }
            $result = [];
            foreach ($queue as $num) {
                if ($num > pow(10, 4)) {
                    $message = _lang('app.element_value_cannot_exceed_10^4') . ' ' . $num;
                    return _api_json(new \stdClass(), ['message' => $message], 400);
                }
                array_push($result, $this->downToZero($num));
            }
            return _api_json($result);
        } catch (\Exception $ex) {
            $message = _lang('app.something_went_wrong');
            return _api_json(new \stdClass(), ['message' => $message], 400);
        }
    }
}
