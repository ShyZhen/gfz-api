<?php

namespace App\Admin\Controllers;

use App\Models\MPWangzheSkin;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPWZSkinController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPWangzheSkin';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPWangzheSkin());

        $table->column('id', __('Id'))->sortable();;
        $table->column('user_id', __('User id'));
        $table->column('skin_patch', __('Skin patch'));
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
        $show = new Show(MPWangzheSkin::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('skin_patch', __('Skin patch'));
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
        $form = new Form(new MPWangzheSkin());

        $form->number('user_id', __('User id'));
        $form->number('skin_patch', __('Skin patch'));

        return $form;
    }
}
