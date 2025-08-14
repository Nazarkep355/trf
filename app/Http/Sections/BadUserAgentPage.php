<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Section;
use function Symfony\Component\Translation\t;

class BadUserAgentPage extends Section implements Initializable
{
    protected $title = 'Юзер агенти';

    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-lightbulb-o');
    }



    public function onDisplay($payload = []) {
        $columns = [
            AdminColumn::text('id', '#')->setWidth('50px')
                ->setHtmlAttribute('class', 'text-center'),
            AdminColumn::text('user_agent', 'Юзер агент')
                ->setSearchable(true),
            AdminColumnEditable::select('status', 'Статус', ['banned' => 'banned',
                'unbanned' => 'unbanned',]),
            AdminColumn::datetime('banned_at', 'Час бану'),
            AdminColumn::text('cloud_id', 'ід правила'),
        ];

        $display = AdminDisplay::datatablesAsync()
//            ->with(['user_by_ip'])
            ->setDisplaySearch(true)
            ->paginate(100)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center');
        $display->getColumns()->getControlColumn();
        return $display;
    }
    public function onCreate($payload = [])
    {
        return $this->onEdit(null, $payload);
    }

    public function onEdit($id = null, $payload = [])
    {
        $form = AdminForm::card()->addBody([
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::text('id', 'ID')
                        ->setReadonly(true)])
                ,
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::text('user_agent', 'Юзер агент')
                        ->setReadonly(false)
                        ->required('Юзерагент обов\'язковий')])

                ,
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::select('status', 'Статус',['banned' => 'banned',
                        'unbanned' => 'unbanned',])
                        ->setReadonly(false)])
                ,
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::datetime('banned_at', 'Час бану')
                        ->setReadonly(true)])
                ,
                AdminFormElement::columns()->addColumn([
                    AdminFormElement::text('id_cloud', 'правило')
                        ->setReadonly(true)])
                ,
            ]
        );

        $form->getButtons()->setButtons([
            'save' => new Save(),
            'save_and_close' => new SaveAndClose(),
            'save_and_create' => new SaveAndCreate(),
            'cancel' => (new Cancel()),
        ]);

        return $form;
    }
    public function isDeletable(Model $model): bool
    {
        return true;
    }
    public function isEditable(Model $model): bool
    {
        return true;
    }

    public function isCheckAccess(): bool
    {
        return true;
    }


    public function __toString(): string
    {
        return 'user_agents';
    }
}
