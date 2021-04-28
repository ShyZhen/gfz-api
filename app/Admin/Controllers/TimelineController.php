<?php

namespace App\Admin\Controllers;

use App\Models\Timeline;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class TimelineController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Timeline';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new Timeline());

        $table->column('id', __('Id'));
//        $table->column('uuid', __('Uuid'));
        $table->column('user_id', __('用户ID'));
        $table->column('title', __('Title'));
        $table->column('poster_list', __('Poster list'))->display(function ($posterList) {
            $temp = json_decode($posterList);
            $html = '<div style="display: flex">';
            foreach ($temp as $url) {
                $html .= "<img src='$url' style='width: 100px'>";
            }

            return $html.'</div>';
        });
        $table->column('collect_num', __('Collect num'));
        $table->column('comment_num', __('Comment num'));
        $table->column('like_num', __('Like num'));
        $table->column('dislike_num', __('Dislike num'));
//        $table->column('deleted', __('Deleted'));
        $table->column('created_at', __('Created at'));
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
        $show = new Show(Timeline::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uuid', __('Uuid'));
        $show->field('user_id', __('User id'));
        $show->field('title', __('Title'));
        $show->field('poster_list', __('Poster list'));
        $show->field('collect_num', __('Collect num'));
        $show->field('comment_num', __('Comment num'));
        $show->field('like_num', __('Like num'));
        $show->field('dislike_num', __('Dislike num'));
        $show->field('deleted', __('删除'));
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
        $form = new Form(new Timeline());

//        $form->text('uuid', __('Uuid'));
//        $form->number('user_id', __('User id'));
        $form->text('title', __('Title'));
        $form->text('poster_list', __('Poster list'));
        $form->number('collect_num', __('Collect num'));
        $form->number('comment_num', __('Comment num'));
        $form->number('like_num', __('Like num'));
        $form->number('dislike_num', __('Dislike num'));
        $form->text('deleted', __('删除'))->default('none');

        return $form;
    }
}
