<?php

namespace App\Admin\Controllers;

use App\Models\Report;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class ReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Report';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new Report());

        $table->column('id', __('ID'))->sortable();
        $table->column('user_id', __('用户ID'));
        $table->column('resource_id', __('资源ID'));
        $table->column('reason', __('理由'));
        $table->column('type', __('类型'));
        $table->column('created_at', __('Created at'))->sortable();

        return $table;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Report::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('resource_id', __('Resource id'));
        $show->field('reason', __('Reason'));
        $show->field('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Report());

        $form->number('user_id', __('User id'));
        $form->number('resource_id', __('Resource id'));
        $form->text('reason', __('Reason'));
        $form->text('type', __('Type'));

        return $form;
    }
}
