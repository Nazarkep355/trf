<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;
use function Symfony\Component\Translation\t;

class CheckLogPage extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = false;

    /**
     * @var string
     */
    protected $title = 'Записи перевірок';

    protected $model = \App\Models\CheckLog::class;

    /**
     * @var string
     */
    protected $alias;

    /**
     * Initialize class.
     */
    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-lightbulb-o');
    }

    public function onDisplay($payload = []) {

        $columns = [
            AdminColumn::text('id', '#')->setWidth('50px')
                ->setHtmlAttribute('class', 'text-center'),
            AdminColumn::text('address', 'IP')
                ->setHtmlAttribute('style', 'max-width: 150px;')
                ->setSearchable(true),
            AdminColumn::text('user_by_ip.country', 'Країна')
                ->setHtmlAttribute('style', 'max-width: 250px;width: 250px;'),
            AdminColumn::datetime('check_time', 'Час')
            ,
            AdminColumn::text('time_scope', 'Інтервал')
                ->setSearchCallback(function ($column, $query, $search) {
                    return $query
                        ->orWhere('user_agent', 'like', '%' . $search . '%');
                })
                ->setHtmlAttribute('style', 'max-width: 30px;width: 30px;'),
            AdminColumn::text('number_of_requests', 'Кількість')
                ->setHtmlAttribute('style', 'max-width: 30px;width: 30px;')
                ->setOrderable(true)
            ,
            AdminColumnEditable::select('user_by_ip.status','Статус',
                ['banned' => 'banned',
                    'unbanned' => 'unbanned',
                    'suspicious' => 'suspicious',
                    'very suspicious' => 'very suspicious',
                    'require inspection' => 'require inspection',
                    'enormous' => 'enormous',
                    'critical' => 'critical',])
                ->setReadonly(false)
                ->setWidth(110),


        ];
        $display = AdminDisplay::datatablesAsync()
            ->with(['user_by_ip'])
            ->setDisplaySearch(true)
            ->setOrder([[0, 'desc']])
            ->paginate(100)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center');
        $display->getColumns()->getControlColumn()
            ->addButton((new \SleepingOwl\Admin\Display\ControlButton(function (\Illuminate\Database\Eloquent\Model $model) {
                return $model->address;
            }, __('Заблокувати юзерагента'), 30))
                ->hideText()
                ->setIcon('fa-solid fa-circle-user')
                ->setHtmlAttribute('style', 'background: red;')
                ->setHtmlAttribute('class', 'btn btn-info uab-btn')
                ->setHtmlAttribute('target', '_blank'))
            ->addButton((new \SleepingOwl\Admin\Display\ControlButton(function (\Illuminate\Database\Eloquent\Model $model) {
                return $model->address;
            }, __('переглянути юзерагентів'), 30))
                ->hideText()
                ->setIcon('fa-solid fa-circle-user')
                ->setHtmlAttribute('style', 'background: blue;')
                ->setHtmlAttribute('class', 'btn btn-info uai-btn')
                ->setHtmlAttribute('target', '_blank'));
        $display->addScript('','/js/checklog.js');
        return $display;
    }
    public function isEditable(Model $model)
    {
        return true;
    }
    public function onEdit(Model $model)
    {

    }

    /**
     * @param bool $checkAccess
     */
    /**
     * @return bool
     */
    public function isCheckAccess(): bool
    {
        return true;
    }


   public function __toString(): string
   {
        return 'Записи перевірок';
   }

}
