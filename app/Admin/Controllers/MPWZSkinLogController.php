<?php

namespace App\Admin\Controllers;

use App\Models\MPWangzheSkinLog;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPWZSkinLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPWangzheSkinLog';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPWangzheSkinLog());

        $table->column('id', __('Id'))->sortable();;
        $table->column('user_id', __('User id'));
        $table->column('num', __('Num'));
        $table->column('type', __('Type'))
            ->select([
                1 => '注册',
                2 => '登录',
                3 => '分享',
                4 => '看视频ad',
                5 => '点击banner',
                9 => '兑换',
            ]);
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
        $show = new Show(MPWangzheSkinLog::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('num', __('Num'));
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
        $form = new Form(new MPWangzheSkinLog());

        $form->number('user_id', __('User id'));
        $form->number('num', __('Num'));
        $form->select('type', __('Type'))->options([
            1 => '注册',
            2 => '登录',
            3 => '分享',
            4 => '看视频ad',
            5 => '点击banner',
            9 => '兑换',
        ]);

        return $form;
    }
}
