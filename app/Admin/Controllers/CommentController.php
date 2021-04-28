<?php

namespace App\Admin\Controllers;

use App\Models\Comment;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class CommentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Comment';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new Comment());

        $table->column('id', __('Id'))->sortable();
        $table->column('type', __('Type'));
//        $table->column('resource_uuid', __('Resource uuid'));
        $table->column('resource_id', __('Resource id'));
        $table->column('parent_id', __('Parent id'));
        $table->column('user_id', __('User id'));
        $table->column('content', __('Content'))->width(400);
        $table->column('like_num', __('Like num'));
        $table->column('dislike_num', __('Dislike num'));
//        $table->column('deleted', __('Deleted'));
        $table->column('created_at', __('Created at'))->sortable();
//        $table->column('updated_at', __('Updated at'));

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
        $show = new Show(Comment::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('resource_uuid', __('Resource uuid'));
        $show->field('resource_id', __('Resource id'));
        $show->field('parent_id', __('Parent id'));
        $show->field('user_id', __('User id'));
        $show->field('content', __('Content'));
        $show->field('like_num', __('Like num'));
        $show->field('dislike_num', __('Dislike num'));
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
        $form = new Form(new Comment());

        $form->text('type', __('Type'));
//        $form->text('resource_uuid', __('Resource uuid'));
//        $form->number('resource_id', __('Resource id'));
//        $form->number('parent_id', __('Parent id'));
//        $form->number('user_id', __('User id'));
        $form->text('content', __('Content'));
        $form->number('like_num', __('Like num'));
        $form->number('dislike_num', __('Dislike num'));
        $form->text('deleted', __('Deleted'))->default('none');

        return $form;
    }
}
