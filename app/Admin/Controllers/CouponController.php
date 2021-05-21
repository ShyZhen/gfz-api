<?php

namespace App\Admin\Controllers;

use App\Models\Coupon;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class CouponController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Coupon';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new Coupon());

        $table->column('id', __('Id'));
        $table->column('name', '标题');
        $table->column('icon', '图标');
        $table->column('banner_pic', '展示图');
        $table->column('url', 'H5短链接');
        $table->column('app_id', '小程序APPID');
        $table->column('path', '小程序路径');
        $table->column('origin_image', '详情页小程序图');
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
        $show = new Show(Coupon::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name','标题');
        $show->field('icon', '图标');
        $show->field('banner_pic', '展示图');
        $show->field('url', 'H5短链接');
        $show->field('app_id', '小程序APPID');
        $show->field('path', '小程序路径');
        $show->field('origin_image', '详情页小程序图');
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
        $form = new Form(new Coupon());

        $form->text('name', __('标题'));
        $form->text('icon', __('图标'));
        $form->text('banner_pic', __('展示图'));
        $form->url('url', __('H5短链接'));
        $form->text('app_id', __('小程序APPID'));
        $form->text('path', __('小程序路径'));
        $form->text('origin_image', __('详情页小程序图'));

        return $form;
    }
}
