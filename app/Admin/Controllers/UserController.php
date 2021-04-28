<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Http\Controllers\AdminController;
use Encore\Admin\Show;
use Encore\Admin\Table;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'User';

    /**
     * Make a table builder.
     *
     * @return Table
     */
    protected function table()
    {
        $table = new Table(new User());

        $table->column('id', __('ID'))->sortable();
//        $table->column('uuid', __('Uuid'));
//        $table->column('email', __('Email'));
        $table->column('mobile', __('手机'));
//        $table->column('password', __('Password'));
//        $table->column('remember_token', __('Remember token'));
        $table->column('name', __('昵称'))->width(200);
//        $table->column('avatar', __('头像'));
        $table->column('gender', __('性别'));
        $table->column('birthday', __('生日'));
//        $table->column('reside_city', __('Reside city'));
        $table->column('bio', __('个性签名'));
        $table->column('closure', __('冻结'));
//        $table->column('is_rename', __('Is rename'));
        $table->column('fans_num', __('粉丝'));
        $table->column('followed_num', __('关注'));
//        $table->column('company', __('Company'));
//        $table->column('company_type', __('Company type'));
//        $table->column('position', __('Position'));
//        $table->column('intro', __('Intro'));
//        $table->column('qq', __('Qq'));
//        $table->column('wechat', __('Wechat'));
//        $table->column('github', __('Github'));
//        $table->column('github_id', __('Github id'));
//        $table->column('wechat_openid', __('Wechat openid'));
        $table->column('created_at', __('创建'))->sortable();
//        $table->column('updated_at', __('更新'));

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('uuid', __('Uuid'));
        $show->field('email', __('Email'));
        $show->field('mobile', __('Mobile'));
//        $show->field('password', __('Password'));
//        $show->field('remember_token', __('Remember token'));
        $show->field('name', __('Name'));
        $show->field('avatar', __('Avatar'));
        $show->field('gender', __('性别'));
        $show->field('birthday', __('Birthday'));
        $show->field('reside_city', __('Reside city'));
        $show->field('bio', __('个性签名'));
        $show->field('closure', __('冻结'));
        $show->field('is_rename', __('改名'));
        $show->field('fans_num', __('Fans num'));
        $show->field('followed_num', __('Followed num'));
        $show->field('company', __('公司'));
        $show->field('company_type', __('行业'));
        $show->field('position', __('职位'));
        $show->field('intro', __('简介'));
        $show->field('qq', __('Qq'));
        $show->field('wechat', __('Wechat'));
        $show->field('github', __('Github'));
        $show->field('github_id', __('Github id'));
        $show->field('wechat_openid', __('Wechat openid'));
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
        $form = new Form(new User());

//        $form->text('uuid', __('Uuid'));
//        $form->email('email', __('Email'));
//        $form->mobile('mobile', __('Mobile'));
//        $form->password('password', __('Password'));
//        $form->text('remember_token', __('Remember token'));
        $form->text('name', __('Name'));
//        $form->image('avatar', __('Avatar'));
        $form->text('gender', __('Gender'))->default('secrecy');
        $form->date('birthday', __('Birthday'))->default(date('Y-m-d'));
//        $form->text('reside_city', __('Reside city'));
        $form->text('bio', __('Bio'));
        $form->text('closure', __('Closure'))->default('none');
//        $form->text('is_rename', __('Is rename'))->default('yes');
//        $form->number('fans_num', __('Fans num'));
//        $form->number('followed_num', __('Followed num'));
//        $form->text('company', __('Company'));
//        $form->text('company_type', __('Company type'));
//        $form->text('position', __('Position'));
//        $form->text('intro', __('Intro'));
//        $form->text('qq', __('Qq'));
//        $form->text('wechat', __('Wechat'));
//        $form->text('github', __('Github'));
//        $form->text('github_id', __('Github id'));
//        $form->text('wechat_openid', __('Wechat openid'));

        return $form;
    }
}
