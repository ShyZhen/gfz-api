<?php

namespace App\Admin\Controllers;

use App\Models\ReportApp;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class ReportAppController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ReportApp';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new ReportApp());

        $table->column('id', __('Id'));
        $table->column('user_id', __('User id'));
        $table->column('content', __('Content'));
        $table->column('poster_list', __('Poster list'))->display(function ($posterList) {
            $temp = json_decode($posterList);
            $html = '<div style="display: flex">';
            foreach ($temp as $url) {
                $html .= "<img src='$url' style='width: 100px'>";
            }

            return $html.'</div>';
        });
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
        $show = new Show(ReportApp::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('content', __('Content'));
        $show->field('poster_list', __('Poster list'));
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
        $form = new Form(new ReportApp());

        $form->number('user_id', __('User id'));
        $form->text('content', __('Content'));
        $form->text('poster_list', __('Poster list'));

        return $form;
    }
}
