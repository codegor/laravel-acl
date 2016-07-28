# LaravelACL
Permissions menegment for big project bilded on laravel. Baseg on yaml permissions file and roles yaml files (users, orders or other substance).

#Example
You can find it in app folder

Этот плагин позволяет не ставить в коде кучу if() для проверки права доступа.
Проверка на серверной части основана на списке разрешенных урлов
а на клиентской части выдается массив данных что нужно скрыть для каждой страницы, что клиенту не мозолило глаза то что ему не доступно

##Пример для удаления закрытых елементов на клиентской части

&lt;script&gt;     Управление доступом к ресурсам сайта
            var ids = <?=json_encode(Sourcemanager::getInstance()->getIds())?>;
                                          Удаляем недоступные элементы страницы
            removeIds();
                                          Событие - после завершения ajax-запроса
            $(document).ajaxComplete(function() {
                removeIds();
            });
                                          Получаем список id и удаляем их
            function removeIds() {
                 console.log('permissions start...', performance.now() + performance.timing.navigationStart);
                for (var i in ids){
                                          Удаляем все кнопки по ID
                    if(ids[i]["id"]){
                        ids[i]["id"].forEach(function(entry) {
                            $('#'+entry).remove();
                        });
                    }
                                          Удаляем все кнопки по классу
                    if(ids[i]["class"]){
                        ids[i]["class"].forEach(function(entry) {
                            $('.'+entry).remove();
                        });
                    }

                    if(ids[i]["selector"]){
                        ids[i]["selector"].forEach(function(entry) {
                            $(entry).remove();
                        });
                    }
                                          Очищаем функции по переменным функций
                    if(ids[i]["func"]){
                        for(var f in ids[i]["func"]){
                            window[ids[i]["func"][f]] = function(){return true;};
                        }
                    }
                }
            }
&lt;/script&gt;


