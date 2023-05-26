<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ManageList extends Command
{

    // определяем сигнатуру команды, которая будет вызывать данный класс и описание команды
    protected $signature = 'list:manage {filename} {action}';
    protected $description = 'Manage list of items in a file';

    public function handle()
    {
        // получаем аргументы команды
        $filename = $this->argument('filename');
        $action = $this->argument('action');

        // читаем файл и создаем массив элементов
        $file = file($filename);
        $items = [];
        foreach ($file as $line) {
            $item = explode('-', $line);
            $items[$item[0]] = (int)$item[1];
        }

        // в зависимости от действия пользователя выполняем соответствующие операции
        switch ($action) {
            case 'add':
                // запрашиваем у пользователя имя и цену нового элемента и добавляем его в массив элементов
                $name = $this->ask('Enter name:');
                $price = (int)$this->ask('Enter price:');
                $items[$name] = $price;
                break;
            case 'update':
                // запрашиваем у пользователя имя элемента, который нужно обновить, и если он найден, то запрашиваем новую цену и новое имя (если нужно)
                // затем производим замену элемента в массиве
                $name = $this->ask('Enter name:');
                if (isset($items[$name])) {
                    $price = (int)$this->ask('Enter price:');
                    $name_new = $this->ask('Enter new name (leave blank to keep the same):');
                    if (!empty($name_new)) {
                        $items[$name_new] = $items[$name];
                        unset($items[$name]);
                        $name = $name_new;
                    }
                    $items[$name] = $price;
                } else {
                    $this->error('Item not found!');
                }
                break;
            case 'delete':
                // запрашиваем у пользователя имя элемента, который нужно удалить, и если он найден, то удаляем его из массива
                $name = $this->ask('Enter name:');
                if (isset($items[$name])) {
                    unset($items[$name]);
                } else {
                    $this->error('Item not found!');
                }
                break;
            case 'subtract':
                // запрашиваем у пользователя сумму, которую нужно вычесть из общей стоимости элементов
                // если эта сумма не превышает общую стоимость, то производим расчет новых цен элементов и заменяем их в массиве
                $sum = (int)$this->ask('Enter sum:');
                $total = array_sum($items);
                if ($total >= $sum) {
                    foreach ($items as $name => $price) {
                        $items[$name] = round($price - $price / $total * $sum);
                    }
                } else {
                    $this->error('Total sum is less than subtract sum!');
                }
                break;
            default:
                // выводим сообщение об ошибке, если действие пользователя не определено
                $this->error('Unknown action!');
        }

        // формируем новый список элементов и записываем его в файл
        $output = [];
        foreach ($items as $name => $price) {
            $output[] = "$name-$price";
        }
        file_put_contents($filename, implode("\n", $output));
    }
}
