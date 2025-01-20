<?php
namespace ExportModule;

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Type\DateTime;
use Bitrix\Crm\ContactTable;
use Bitrix\Crm\FieldMultiTable;
use Exception;

class CRMContactExport
{
    private string $filePath;

    /**
     * @throws Exception
     */
    public function __construct($filePath)
    {
        if (!Loader::includeModule("crm")) {
            throw new Exception("Модуль CRM не подключен!");
        }

        // Устанавливаем путь к файлу
        $this->filePath = $filePath;

        // Убедимся, что директория существует
        $dir = Directory::createDirectory(dirname($this->filePath));
        if (!$dir->isExists()) {
            throw new Exception("Не удалось создать директорию для сохранения файла CSV.");
        }
    }

    /**
     * Экспорт контактов в CSV-файл
     */
    public function export(): void
    {
        // Открываем файл для записи
        $file = new File($this->filePath);
        if ($file->isExists()) {
            $file->delete();
        }
        $file->putContents("");

        // Добавляем заголовки CSV
        $this->writeToCSV(['ID', 'Имя', 'Фамилия', 'Отчество', 'Телефон', 'Email', 'Дата создания']);

        // Запрос данных контактов
        $contacts = ContactTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'DATE_CREATE'],
            'filter' => ['=ACTIVE' => 'Y'], // Только активные контакты, при необходимости фильтр можно расширить
        ]);

        while ($contact = $contacts->fetch()) {
            // Получение дополнительных данных, таких как телефоны и email
            $multiFields = $this->getMultiFields($contact['ID']);

            // Добавляем данные в CSV
            $this->writeToCSV([
                $contact['ID'],
                $contact['NAME'],
                $contact['LAST_NAME'],
                $contact['SECOND_NAME'],
                $multiFields['PHONE'] ?? '',
                $multiFields['EMAIL'] ?? '',
                $contact['DATE_CREATE'] instanceof DateTime
                    ? $contact['DATE_CREATE']->format('Y-m-d H:i:s')
                    : '',
            ]);
        }

        echo "Экспорт завершен. Файл доступен по пути: {$this->filePath}\n";
    }

    /**
     * Получение телефонов и email контакта из b_crm_field_multi
     *
     * @param int $contactId
     * @return array
     */
    private function getMultiFields(int $contactId): array
    {
        $result = ['PHONE' => '', 'EMAIL' => ''];

        $multiFields = FieldMultiTable::getList([
            'select' => ['TYPE_ID', 'VALUE'],
            'filter' => [
                '=ENTITY_ID' => 'CONTACT',
                '=ELEMENT_ID' => $contactId,
                '=TYPE_ID' => ['PHONE', 'EMAIL'],
            ],
        ]);

        while ($field = $multiFields->fetch()) {
            if ($field['TYPE_ID'] === 'PHONE' && empty($result['PHONE'])) {
                $result['PHONE'] = $field['VALUE']; // Первый телефон
            } elseif ($field['TYPE_ID'] === 'EMAIL' && empty($result['EMAIL'])) {
                $result['EMAIL'] = $field['VALUE']; // Первый email
            }
        }

        return $result;
    }

    /**
     * Запись строки в CSV файл
     *
     * @param array $row
     */
    private function writeToCSV(array $row): void
    {
        $file = fopen($this->filePath, 'a'); // Открытие файла в режиме добавления
        fputcsv($file, $row, ';'); // Используем ';' как разделитель
        fclose($file);
    }
}