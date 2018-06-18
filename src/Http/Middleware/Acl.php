<?php
  
  namespace Codegor\Acl\Http\Middleware;
  
  use Closure;
  use Acl as LaravelAcl;
  
  class Acl {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null) {
      /**
       * что мне нужно тут сделать?
       * получить все роуты роли, и тип все или ничего кроме
       * получить роль пользователя
       * проверить есть ли текущий name в списке или разрешон он, если разрешено то далее, если нет то сообщение о запрете (страницы не существует типа)
       *
       * еще нужна команда для создания ролей...
       * подправить конфиг по умолчанию
       * нужен трейт для модели юзерс
       *
       */
      
      if (LaravelAcl::canAccess(auth()->user(), $request->route()->getName()))
        return $next($request);
      else {
        if ($request->isJson() || $request->wantsJson()) {
          return response()->json([
            'error' => [
              'status_code' => 401,
              'code'        => 'NOT_FOUND',
              'description' => 'Point not found'
            ],
          ], 401);
        }
        return abort(401, 'Page not found');
      }
    }
  }
