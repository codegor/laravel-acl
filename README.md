# LaravelACL
Permissions menegment for big project bilded on laravel. Baseg on yaml permissions file and roles yaml files (users, orders or other substance).

#Example
You can find it in app folder

���� ������ ��������� �� ������� � ���� ���� if() ��� �������� ����� �������.
�������� �� ��������� ����� �������� �� ������ ����������� �����
� �� ���������� ����� �������� ������ ������ ��� ����� ������ ��� ������ ��������, ��� ������� �� �������� ����� �� ��� ��� �� ��������

##������ ��� �������� �������� ��������� �� ���������� �����

&lt;script&gt;     ���������� �������� � �������� �����
            var ids = <?=json_encode(Sourcemanager::getInstance()->getIds())?>;
                                          ������� ����������� �������� ��������
            removeIds();
                                          ������� - ����� ���������� ajax-�������
            $(document).ajaxComplete(function() {
                removeIds();
            });
                                          �������� ������ id � ������� ��
            function removeIds() {
                 console.log('permissions start...', performance.now() + performance.timing.navigationStart);
                for (var i in ids){
                                          ������� ��� ������ �� ID
                    if(ids[i]["id"]){
                        ids[i]["id"].forEach(function(entry) {
                            $('#'+entry).remove();
                        });
                    }
                                          ������� ��� ������ �� ������
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
                                          ������� ������� �� ���������� �������
                    if(ids[i]["func"]){
                        for(var f in ids[i]["func"]){
                            window[ids[i]["func"][f]] = function(){return true;};
                        }
                    }
                }
            }
&lt;/script&gt;


