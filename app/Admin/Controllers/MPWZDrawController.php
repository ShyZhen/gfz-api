<?php

namespace App\Admin\Controllers;

use App\Models\MPWangzheDraw;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class MPWZDrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MPWangzheDraw';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new MPWangzheDraw());

        $table->column('id', __('Id'))->sortable();;
        $table->column('limit_user', __('Limit user'));
        $table->column('title', __('Title'));
        $table->column('image', __('Image'));
        $table->column('winner_id', __('Winner id'));
        $table->column('type', __('Type'))
                ->select([
                    0 => '进行中',
                    1 => '已结束',
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
        $show = new Show(MPWangzheDraw::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('limit_user', __('Limit user'));
        $show->field('title', __('Title'));
        $show->field('image', __('Image'));
        $show->field('winner_id', __('Winner id'));
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
        $form = new Form(new MPWangzheDraw());

        $form->number('limit_user', __('Limit user'))->default(1000);
        $form->text('title', __('Title'));
        $form->text('image', __('Image'));

        return $form;
    }
}
