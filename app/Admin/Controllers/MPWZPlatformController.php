<?php

namespace App\Admin\Controllers;

use App\Models\MPWangzhePlatform;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPWZPlatformController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPWangzhePlatform';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPWangzhePlatform());

        $table->column('id', __('Id'));
        $table->column('uuid', __('Uuid'));
        $table->column('app_id', __('App id'));
        $table->column('app_secret', __('App secret'));
        //$table->column('deleted', __('Deleted'));
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
        $show = new Show(MPWangzhePlatform::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uuid', __('Uuid'));
        $show->field('app_id', __('App id'));
        $show->field('app_secret', __('App secret'));
        $show->field('deleted', __('Deleted'));
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
        $form = new Form(new MPWangzhePlatform());

        $form->text('uuid', __('Uuid'))->value(self::uuid('app-'));
        $form->text('app_id', __('App id'));
        $form->text('app_secret', __('App secret'));
        // $form->text('deleted', __('Deleted'))->default('none');
        return $form;
    }

    protected static function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);

        return $prefix . $uuid;
    }
}
