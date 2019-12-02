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
            <div class="col-md-3">用户编号：{{$order['user_id']}}</div>
            <div class="col-md-3">订单编号：{{$order['order_no']}}</div>
            <div class="col-md-3">商品总价：{{$order['product_amount_total']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户姓名：{{$order['address']['name']}}</div>
            <div class="col-md-3">商品数量：{{$order['product_count']}}</div>
            <div class="col-md-3">快递费用：{{$order['logistics_fee']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单手机：{{$order['address']['phone']}}</div>
            <div class="col-md-3">是否状态：<span  style="color: green">{{$order['status']}}</span></div>
            <div class="col-md-3">订单金额：{{$order['order_amount_total']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单备注：<span class="label-danger">{{$order['remark']}}</span></div>
            <div class="col-md-3">下单时间：{{$order['created_at']}}</div>
            <div class="col-md-3">更新时间：{{$order['updated_at']}}</div>
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
                    <td class="col-xs-1">单价</td>
                    <td class="col-xs-1">总价</td>
                    <td class="col-xs-3">评价</td>
                </th>

                @foreach($order['goods'] as $goods)
                    <tr class="row">
                        <td><img src="{{$goods['pic_url']}}" style="width:100px" alt=""></td>
                        <td>{{$goods['name']}}</td>
                        <td>{{$goods['pivot']['sku']}}</td>
                        <td>{{$goods['pivot']['product_count']}}</td>
                        <td>{{$goods['pivot']['product_price']}}</td>
                        <td>{{$goods['pivot']['product_price'] * $goods['pivot']['product_count']}}</td>
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
                <tr><td class="col-xs-1">收货地址：</td><td>{{$order['address']['province']}} {{$order['address']['city']}} {{$order['address']['county']}} {{$order['address']['detail_info']}}</td></tr>
                <tr><td class="col-xs-1">收件人：</td><td>{{$order['address']['name']}}</td></tr>
                <tr><td class="col-xs-1">手机：</td><td>{{$order['address']['phone']}}</td></tr>
                <tr><td class="col-xs-1">邮编：</td><td>{{$order['address']['postal_code']}}</td></tr>
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">订单日志</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td class="col-xs-6">操作时间</td>
                    <td class="col-xs-6">操作事件</td>
                </th>

                @foreach($order['event_logs'] as $log)
                    <tr class="row">
                        <td>{{$log['created_at']}}</td>
                        <td>{{$log['event']}}</td>
                    </tr>
                @endforeach
            </table>

        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>
