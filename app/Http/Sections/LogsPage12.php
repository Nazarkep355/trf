<?php

namespace App\Http\Sections;

use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Facades\Display;
use SleepingOwl\Admin\Display\Column\Text;
//use SleepingOwl\Admin\Facades\Column;

//use SleepingOwl\Admin\Form\Columns\Column;
use SleepingOwl\Admin\Facades\TableColumn;
use SleepingOwl\Admin\Form\Columns\Column;
use SleepingOwl\Admin\Model\ModelConfiguration;
use SleepingOwl\Admin\Section;

class LogsPage12 extends Section
{

    protected $title = 'LogLines';

    /**
     * Alias used in URLs (e.g. /admin/posts)
     * @var string
     */
    protected $alias = 'logs';
    public function registerModel(ModelConfiguration $model) {
        $model->setTitle('logs')->setAlias('logs');

        // ─────────────────────────────────────────────────────────────────────────────
        // LIST (Index) VIEW: DataTable with checkboxes, columns, and action buttons
        // ─────────────────────────────────────────────────────────────────────────────

        $model->onDisplay(function () {
            // Define an array of columns
            $columns = [
                TableColumn::text('id', '#')->setHtmlAttribute('class', 'text-center'),
                TableColumn::text('address', 'IP адрес')->setHtmlAttribute('class', 'text-center'),
                TableColumn::datetime('timestamp','Час')->setHtmlAttribute('class', 'text-center'),
                TableColumn::text('country','Країна')->setHtmlAttribute('class', 'text-center'),
                TableColumn::text('url','URL')->setHtmlAttribute('class', 'text-center'),
                TableColumn::text('user_agent','User Agent')->setHtmlAttribute('class', 'text-center'),
//                TableColumn::
//                ('delete', 'Delete')
//                    ->setHtmlAttribute('class', 'btn btn-danger btn-sm')
//                    ->setIcon('fa fa-plus')
//                    ->setConfirmText('Are you sure you want to delete selected logs?')
//                    ->setMethod('DELETE'),
            ];

            // Initialize a DataTables display
            $display = Display::datatables()
                ->setName('logs_datatable')                                    // optional ID
                ->setHtmlAttribute('class', 'table table-striped')             // Bootstrap styling
                ->paginate(100)
//                ->setOrder('timestamp','desc')// rows per page
                ->setColumns($columns);                                         // Attach the array of columns

            // (Optional) Bulk-delete action on selected rows
            $display->setActions([
//                TableColumn::action('delete', 'Delete')
//                    ->setHtmlAttribute('class', 'btn btn-danger btn-sm')
//                    ->setIcon('fa fa-plus')
////                    ->setConfirmText('Are you sure you want to delete selected logs?')
//                    ->setMethod('DELETE'),
                // ...existing code...
            ]);

            return $display;
        });

        // ─────────────────────────────────────────────────────────────────────────────
        // CREATE & EDIT FORM
        // ─────────────────────────────────────────────────────────────────────────────
//        $model->onCreateAndEdit(function () {
//            return AdminForm::panel()->addBody([
//                AdminFormElement::text('title',     'Title')->required(),
//                AdminFormElement::select('author_id', 'Author')
//                    ->setModelForOptions(\App\User::class)
//                    ->setDisplay('name'),
//                AdminFormElement::ckeditor('content', 'Content'),
//                AdminFormElement::datetime('created_at', 'Created at')
//                    ->setFormat('Y-m-d H:i:s'),
//            ]);
//        });
    }


}

