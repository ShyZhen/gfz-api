<?php

namespace App\Admin\Controllers;

use App\Models\MPVideoItem;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPVideoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPVideoItem';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPVideoItem());

        $table->column('id', __('Id'))->sortable();
        $table->column('vid', __('Vid'));
        $table->column('title', __('Title'));
        $table->column('image', __('Image'));
        $table->column('desc', __('Desc'));
        $table->column('type', __('类型'));
        $table->column('vip_type', __('是否vip'))
            ->switch([
                'on'  => ['value' => 1, 'text' => '是', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '否', 'color' => 'default'],
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
        $show = new Show(MPVideoItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('vid', __('Vid'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('desc', __('Desc'));
        $show->field('type', __('Type'));
        $show->field('vip_type', __('Vip type'));
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
        $form = new Form(new MPVideoItem());

        $form->text('vid', __('Vid'));
        $form->text('title', __('Title'));
        $form->text('image', __('Image'));
        $form->text('desc', __('Desc'));
        $form->select('type', __('Type'))->options([
            1 => '搞笑',
            2 => '小品',
            3 => '电影',
            4 => '美女',
            5 => '新闻',
            6 => '科技',
            7 => '悬疑',
        ]);
        $form->switch('vip_type', __('Vip type'));

        return $form;
    }
}
