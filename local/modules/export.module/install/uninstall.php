<?php
use Bitrix\Main\ModuleManager;

class export_module extends CModule
{
    public function DoUninstall()
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);
        // Логика удаления модуля
    }
}