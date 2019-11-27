<style>
    .middle {
        float: none;
        display: inline-block;
        vertical-align: middle;
    }
</style>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">订单信息</h3>
    </div>

    <div class="box-body">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户编号：170379</div>
            <div class="col-md-3">订单编号：GM2019112415470155707</div>
            <div class="col-md-3">商品总价：272</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户昵称：后海</div>
            <div class="col-md-3">商品数量：8</div>
            <div class="col-md-3">快递费用：14</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单手机：17600296638</div>
            <div class="col-md-3">是否支付：<span  style="color: green">已支付</span></div>
            <div class="col-md-3">订单金额：286</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单状态：已评价</div>
            <div class="col-md-3">下单时间：2019-11-24 15:47:01</div>
            <div class="col-md-3">更新时间：2019-11-24 15:47:01</div>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">商品信息</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td class="col-xs-2">图标</td>
                    <td class="col-xs-2">名称</td>
                    <td class="col-xs-3">规格</td>
                    <td class="col-xs-1">数量</td>
                    <td class="col-xs-1">总价</td>
                    <td class="col-xs-3">评价</td>
                </th>
                @foreach($envs as $env)
                    <tr class="row">
                        <td><img src="https://dcdn.it120.cc/2019/09/11/82593b70-4ad4-4d9e-9802-87a90f756796.jpeg" class="img-thumbnail" style="width:100px" alt=""></td>
                        <td>糯香猪蹄【小份/180g】</td>
                        <td>口味:麻辣,份量:300g</td>
                        <td>3</td>
                        <td>120</td>
                        <td><span class="label label-primary">5分</span> &nbsp;&nbsp;非常愉快的一次购物！非常愉快的一次购物！非常愉快的一次购物！非常愉快的一次购物！</td>
                    </tr>
                @endforeach
            </table>

        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">快递信息</h3>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <tr><td class="col-xs-1">收货地址：</td><td>广东省 广州市 海珠区 新港中路397号</td></tr>
                <tr><td class="col-xs-1">收件人：</td><td>饶后海</td></tr>
                <tr><td class="col-xs-1">手机：</td><td>17600296638</td></tr>
                <tr><td class="col-xs-1">邮编：</td><td>510000</td></tr>
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">订单记录</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td class="col-xs-6">操作时间</td>
                    <td class="col-xs-6">操作事件</td>
                </th>
                @foreach($envs as $env)
                    <tr class="row">
                        <td>2019-11-24 15:47:01</td>
                        <td>下单</td>
                    </tr>
                @endforeach
            </table>

        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>
