<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request){
    
    //Проверяем доступ по ролям
    if (Auth::check() // Если авторизован + исключаем общие для всех страницы
        && !in_array($request->fullUrl(),
                    [
                        $request->root(), //usercontroller@index - т.е. логин
                    ])
    ){
        $rights = Sourcemanager::getInstance();
        if(!$rights->checkUri($request->path(), $request->method()) )
//                return Response::make('404 Not Found', 404);
                return Response::make('Этой страницы не существует или вы не имеете прав допступа.', 200);

    }
});
