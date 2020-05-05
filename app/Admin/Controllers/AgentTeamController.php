<?php

namespace App\Admin\Controllers;

use App\Models\AgentBill;
use App\Models\AgentTeam;
use App\Models\AgentTeamUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class AgentTeamController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '代理团队';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentTeam);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('编号'));
        $grid->column('user_id', __('用户编号'));
        $grid->column('user_info.nickname', __('用户'));
        $grid->column('status', __('状态'))->using(AgentTeam::getStatus());;
        $grid->column('created_at', __('创建时间'));

        $grid->disableCreateButton();
        $grid->expandFilter();
        $grid->actions(function ($gird) {
            $gird->disableDelete();
            $gird->disableEdit();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('user_id','用户编号');
                $filter->like('user_info.nickname','用户昵称');
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('status','状态')->select(AgentTeam::getStatus());
                $filter->between('created_at','申请时间')->datetime();
            });
        });

        return $grid;
    }

    public function show($id, Content $content)
    {
        $data = AgentTeam::find($id);

        $bills = AgentBill::whereHas('agent_team_user', function ($query) use($id) {
            $query->where('team_id',$id);
        })->with('user_info')->get();

        $view = view('admin.agent.agent_team',compact('data'),compact('bills'));

        return $content
            ->title($this->title)
            ->description('详情...')
            ->row(function (Row $row) use ($view) {
                $row->column(12,function (Column $column) use ($view) {
                    $column->append($view);
                });
            });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(AgentTeam::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('qrcode', __('Qrcode'));
        $show->field('status', __('Status'));
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
        $form = new Form(new AgentTeam);

        $form->number('user_id', __('User id'));
        $form->text('qrcode', __('Qrcode'));
        $form->number('status', __('Status'));

        return $form;
    }
}
