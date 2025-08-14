<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminDisplay;
use AdminColumnFilter;
use AdminForm;
use AdminFormElement;
use App\Models\LogLine;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Display\Column\Editable\EditableColumn;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Section;

/**
 * Class LogsPage
 *
 * @property LogLine $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class LogsPage extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = false;

    /**
     * @var string
     */
    protected $title = 'Запити';

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

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {
        $columns = [
            AdminColumn::text('id', '#')->setWidth('50px')->setHtmlAttribute('class', 'text-center'),
            AdminColumn::text('address', 'IP')
                ->setHtmlAttribute('style', 'width: 120px;max-width: 120px;')
                ->setSearchable(true),
            AdminColumn::text('country', 'Країна')
                ->setHtmlAttribute('style', 'max-width: 200px;width: 200px;'),
            AdminColumn::text('url', 'URL', 'created_at')
                ->setSearchCallback(function ($column, $query, $search) {
                    return $query
                        ->orWhere('url', 'like', '%' . $search . '%');
                })
            ,
            AdminColumn::text('user_agent', 'User Agent')
                ->setSearchCallback(function ($column, $query, $search) {
                    return $query
                        ->orWhere('user_agent', 'like', '%' . $search . '%');
                }),
            AdminColumn::datetime('timestamp', 'Час')
                ->setWidth('160px')
//                ->setOrderable(function ($query, $direction) {
//                    $query->orderBy('updated_at', $direction);
//                })
                ->setSearchable(false)
            ,
        ];

        $display = AdminDisplay::datatables()
//            ->setName('firstdatatables')
            ->setOrder([[0, 'desc']])
            ->setDisplaySearch(true)
            ->paginate(100)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center');

//        $display->setColumnFilters([
//            AdminColumnFilter::select()
//                ->setModelForOptions(LogLine::class, 'name')
//                ->setLoadOptionsQueryPreparer(function ($element, $query) {
//                    return $query;
//                })
//                ->setDisplay('Country')
//                ->setColumnName('country')
//                ->setPlaceholder('All countries')
//            ,
//        ]
//        );
//        $display->getColumnFilters()->setPlacement('card.heading');

        return $display;
    }

    /**
     * @param int|null $id
     * @param array $payload
     *
     * @return FormInterface
     */
    public function onEdit($id = null, $payload = [])
    {
        $form = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('url', 'URL')
                ->setReadonly(true)])
                ,
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('address', 'IP')
                ->setReadonly(true)])
                    ,
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('url', 'URL')
                ->setReadonly(true)])
                        ,
            AdminFormElement::columns()->addColumn([
                AdminFormElement::text('url', 'URL')
                ->setReadonly(true)])
                            ,
            AdminFormElement::datetime('timestamp', 'Час')
                ->setFormat('d.m.Y H:i:s')
                ->setVisible(true)
                ->setReadonly(true)
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
    public function isEditable(Model $model)
    {
        return true;
    }

    /**
     * @return FormInterface
     */
    public function onCreate($payload = [])
    {
        return $this->onEdit(null, $payload);
    }

    public function onEditAttempt()
    {
        \Log::info('onEditAttempt called');
    }

    /**
     * @return bool
     */
    public function isDeletable(Model $model)
    {
        return true;
    }

    /**
     * @return void
     */
    public function onRestore($id)
    {
        // remove if unused
    }

    public function __toString(): string
    {
        return 'logs';
    }
}
