<?php
use \Illuminate\Http\Request;
/**
 *  калбек должен возвращать false если условиям не удовлетворило и труе если удовлетворено
 *   входные параметры - 1 - масcив ["url", "method", "length", "callback"];
 */

function checkDeleteRowPSO($p){
    if(Input::is('project/budget-save/*') && "DELETE" == Input::method()){
        $input = Input::all();
        $new_input = [];
        foreach ($input as $row_id) {
            if('s' != $row_id[0]){
                //в файле прав едет вычет входящих
                //сделать валидацию!!!
                $m = Title::find($row_id);
                if(str_contains(substr($m->code, 0, 2), '01')){
                    continue;
                }
            } else {
                $row_id_cut = substr($row_id, 1);
                if(str_contains(substr($row_id_cut, 0, 2), '01')){
                    continue;
                }
            }

            $new_input[] = $row_id;
        }
        Input::replace($new_input);
    }
    return false;
}