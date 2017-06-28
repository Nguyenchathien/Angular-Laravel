<?php
/*
 * Copyright 2015 SPCVN Co., Ltd.
 * All right reserved.
*/

/**
 * @Author: Nguyen Chat Hien
 * @Date:   2016-08-30 13:38:11
 * @Last Modified by:   Nguyen Chat Hien
 * @Last Modified time: 2016-08-31 13:32:22
 */

namespace App\Http\Controllers;

use Response;
use App\User;
use App\Example;
use Illuminate\Http\Request;

class ExamplesController extends Controller
{

    public function __construct() {
        // $this->middleware('jwt.auth');
        $this->middleware('jwt.auth', ['except' => ['authenticate', 'index', 'show']]);
    }

    public function index(Request $request) {

        $search_item = $request->input('search');
        $limit = $request->input('limit')?$request->input('limit'):5;

        if ($search_item) {
            $examples = Example::OrderBy('id', 'DESC')->where('note', 'LIKE', "%$search_item%")->with(
                array('User'=>function($query) {
                    $query->select('id', 'name');
                }))->select('id', 'note', 'user_id')->paginate($limit);
            $examples->appends(array(
                    'search' => $search_item,
                    'limit' => $limit
                ));
        } else {
            $examples = Example::orderBy('id', 'DESC')->with(
                array('User'=>function($query){
                    $query->select('id','name');
                })
            )->select('id', 'note', 'user_id')->paginate($limit);

            $examples->appends(array(
                'limit' => $limit
            ));
        }

        return Response::json($this->transformCollection($examples), 200);
    }

    /**
     * { item_description }
     */
    public function show($id) {
        $example = Example::with(
            array('User' => function($query) {
                    $query -> select('id', 'name');
            })
            )->find($id);

        if(!$example) {
            return Response::json([
                'error' => [
                    'message' => 'Example dose not exits']]
                , 404);
        }

        $previous = Example::where('id', '<', $example->id)->min('id');

        $next = Example::where('id', '>', $example->id)->min('id');

        return Response::json([
            'previous_example_id'=> $previous,
            'next_example_id'=> $next,
            'data' => $this->transform($example)], 200);
    }

    public function store(Request $request) {
        if(! $request->body or ! $request->user_id){
            return Response::json([
                'error' => [
                    'message' => 'Please Provide Both body and user_id'
                ]
            ], 422);
        }
        $example = Example::create($request->all());

        return Response::json([
                'message'   => 'Example Created Succesfully',
                'data'      => $this->transform($example)
        ]);
    }

    /**
     * { item_description }
     */
    private function transformCollection($examples) {
        $exampleArray = $examples->toArray();
        return [
            'total' => $exampleArray['total'],
            'per_page' => intval($exampleArray['per_page']),
            'current_page' => $exampleArray['current_page'],
            'last_page' => $exampleArray['last_page'],
            'next_page_url' => $exampleArray['next_page_url'],
            'prev_page_url' => $exampleArray['prev_page_url'],
            'from' => $exampleArray['from'],
            'to' =>$exampleArray['to'],
            'data' => array_map([$this, 'transform'], $exampleArray['data'])
        ];
    }

    /**
     * { item_description }
     */
    private function transform($example) {
        return [
            'example_id'    => $example['id'],
            'example_note'  => $example['note'],
            'submitted_by'  => $example['user']['name']
            // 'submitted_by'  => $example['user_id']
        ];
    }

    /**
     * { item_description }
     */
    public function update(Request $request, $id)
    {
        if(! $request->body or ! $request->user_id){
            return Response::json([
                'error' => [
                'message' => 'Please Provide Both body and user_id'
                ]
            ], 422);
        }
        $example = Example::find($id);
        $example->body = $request->body;
        $example->user_id = $request->user_id;
        $example->save();
        return Response::json([
            'message' => 'Example Updated Succesfully'
        ]);
    }

    /**
     * { item_description }
     */
    public function destroy($id)
    {
        Example::destroy($id);
    }

}
