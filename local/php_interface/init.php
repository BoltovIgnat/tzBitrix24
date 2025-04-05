<?php

\Bitrix\Main\EventManager::getInstance()->addEventHandler('main', 'onProlog', function () {
    // Проверим является ли страница детальной карточкой CRM через функционал роутинга компонентов
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

    $uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());

    $path = $uri->getPath();
    $assetManager = \Bitrix\Main\Page\Asset::getInstance();

    // Подключаем js файл

    $userId = \Bitrix\Main\Engine\CurrentUser::get()->getId();
    $rsUser = CUser::GetByID($userId);
    $arGroups = CUser::GetUserGroup($userId);
    $jsGroups = \Bitrix\Main\Web\Json::encode(
        $arGroups,
        JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
    );

    $arUser = $rsUser->Fetch();
    $varFIO = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
    if ($path == '/crm/company/list/'){
        $assetManager->addString('
        <script>
        let Groups = '.$jsGroups.';
        let isAdmin = Groups.includes(`1`);
        BX.ready(function () {
            console.log(`'.$path.'`);
        });
        BX.addCustomEvent("Grid::ready", function (gridData) {
            if(!isAdmin){
                let filter = BX.Main.filterManager.getById(gridData.containerId);
                let values = filter.getFilterFieldsValues();
                
                values["ASSIGNED_BY_ID"] = [`'.$userId.'`];
                values["ASSIGNED_BY_ID_label"] = [`'.$varFIO.'`];
                filter.getApi().setFields(values);
                filter.getApi().apply();
            }  
        });
        
        BX.addCustomEvent("Grid::beforeRequest", function (gridData, argse) {
            if(!isAdmin){
                let filter = BX.Main.filterManager.getById(argse.gridId);
                let values = filter.getFilterFieldsValues();
                let bFlag = false
                if (!values["ASSIGNED_BY_ID"].includes(`'.$userId.'`)){
                    values["ASSIGNED_BY_ID"] = [`'.$userId.'`];
                    bFlag = true; 
                }
                if (!values["ASSIGNED_BY_ID_label"].includes(`'.$varFIO.'`)){
                    values["ASSIGNED_BY_ID_label"] = [`'.$varFIO.'`];
                    bFlag = true;
                }
                if (bFlag){
                    filter.getApi().setFields(values);
                    filter.getApi().apply();
                }
            }
            
        });
        </script>
    ');
    }


});


