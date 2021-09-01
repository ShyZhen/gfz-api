<?php

namespace App\Admin\Controllers;

use App\Models\MPWangzheSkinConvert;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPWZSkinConvertController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPWangzheSkinConvert';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPWangzheSkinConvert());

        $table->column('id', __('Id'));
        $table->column('user_id', __('User id'));
        $table->column('user_uuid', __('User uuid'));
        $table->column('convert_num', __('Convert num'));
        $table->column('status', __('Status'));
        $table->column('created_at', __('Created at'));
        $table->column('updated_at', __('Updated at'));

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
        $show = new Show(MPWangzheSkinConvert::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('user_uuid', __('User uuid'));
        $show->field('convert_num', __('Convert num'));
        $show->field('status', __('Status'));
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
        $form = new Form(new MPWangzheSkinConvert());

        $form->display('user_id', __('User id'));
        $form->display('user_uuid', __('User uuid'));
        $form->display('convert_num', __('Convert num'));
        $form->select('status', __('Status'))->options([
            'wait' => '待处理',
            'success' => '已完成',
        ]);

        return $form;
    }
}
