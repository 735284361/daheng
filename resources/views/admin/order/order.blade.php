
@if (!in_array($data['status'], [
    App\Models\Order::STATUS_ORDER_CLOSE,
    App\Models\Order::STATUS_COMPLETED,
]))
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">操作</h3>
    </div>

    <div class="box-body">
        <div class="container">
            <div class="col-md-12">
                <button class="btn btn-success" onclick="refund('设置为已支付', '即将修改该笔订单状态为已付款，请确保您已经线下收款, 是否继续?', 'SET_PAID')">退款</button>
                @if ($data['status'] == App\Models\Order::STATUS_UNPAID)
                    <button class="btn btn-success" onclick="updateOrderStatus('设置为已支付', '即将修改该笔订单状态为已付款，请确保您已经线下收款, 是否继续?', 'SET_PAID')">设为已支付</button>
                @elseif ($data['status'] == App\Models\Order::STATUS_PAID || $data['status'] == App\Models\Order::STATUS_SHIPPED)
                    <button class="btn btn-success" onclick="delivery()">发货</button>
                    <button class="btn btn-success" onclick="updateOrderStatus('设置为确认收货', '即将修改该笔订单状态为确认收货，设置后订单状态将不能更改，是否继续？', 'SET_RECEIVED')">设置为确认收货</button>
                @elseif ($data['status'] == App\Models\Order::STATUS_RECEIVED)
                    <button class="btn btn-success" onclick="delivery()">发货</button>
                    <button class="btn btn-success" onclick="updateOrderStatus('设置为交易成功', '即将修改该笔订单状态为交易成功，设置后订单状态将不能更改，是否继续？', 'SET_COMPLETED')">设为交易成功</button>
                @endif
                    <button class="btn btn-warning" onclick="updateOrderStatus('关闭订单', '是否确认关闭订单？', 'SET_CLOSED')">关闭订单</button>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
@endif

<div class="box box-default">
    <div class="box-header with-border">
        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" aria-label="...">
                    </span>
                    <span class="input-group-addon" id="basic-addon3">
                        Q弹翅尖&nbsp;/&nbsp;口味:麻辣 重量:小份180g&nbsp;/&nbsp;<span style="color:red">可退数量：1</span></span>
                    <div class="input-group-btn">
                        <input type="number" class="form-control" aria-label="..." value="1" max-value="2">
                    </div>
                </div><!-- /input-group -->
            </div><!-- /.col-lg-6 -->
        </div><!-- /.row -->
        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" aria-label="...">
                    </span>
                    <span class="input-group-addon" id="basic-addon3">Q弹翅尖&nbsp;/&nbsp;口味:麻辣 重量:小份180g&nbsp;/&nbsp;可退数量：1</span>
                    <div class="input-group-btn">
                        <input type="number" class="form-control" aria-label="..." value="1" max-value="2">
                    </div>
                </div><!-- /input-group -->
            </div><!-- /.col-lg-6 -->
        </div><!-- /.row -->
        <div class="row">
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" aria-label="...">
                    </span>
                    <span class="input-group-addon" id="basic-addon3">Q弹翅尖&nbsp;/&nbsp;口味:麻辣 重量:小份180g&nbsp;/&nbsp;可退数量：1</span>
                    <div class="input-group-btn">
                        <input type="number" class="form-control" aria-label="..." value="1" max-value="2">
                    </div>
                </div><!-- /input-group -->
            </div><!-- /.col-lg-6 -->
        </div><!-- /.row -->
    </div>
</div>
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">订单信息</h3>
    </div>

    <div class="box-body">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户编号：{{$data['user_id']}}</div>
            <div class="col-md-3">订单编号：{{$data['order_no']}}</div>
            <div class="col-md-3">商品总价：{{$data['product_amount_total']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户姓名：{{$data['address']['name']}}</div>
            <div class="col-md-3">商品数量：{{$data['product_count']}}</div>
            <div class="col-md-3">快递费用：{{$data['logistics_fee']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单手机：{{$data['address']['phone']}}</div>
            <div class="col-md-3">订单状态：<span  style="color: green">{{App\Models\Order::getStatus($data['status'])}}</span></div>
            <div class="col-md-3">订单金额：{{$data['order_amount_total']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">订单备注：<span class="label-danger">{{$data['remark']}}</span></div>
            <div class="col-md-3">下单时间：{{$data['created_at']}}</div>
            <div class="col-md-3">更新时间：{{$data['updated_at']}}</div>
        </div>
        <!-- /.table-responsive -->
    </div>

    <div class="box-header with-border">
        <h3 class="box-title">代理信息</h3>
    </div>
    <div class="box-body">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">代理商：<mark>{{$data['orderAgent']['agent']['user']['nickname']}}</mark></div>
            <div class="col-md-3">代理分成：<mark>{{$data['orderAgent']['commission']}}</mark></div>
            <div class="col-md-3">分成状态：<mark>@if($data['orderAgent']['status']){{App\Models\AgentOrderMaps::getStatus($data['orderAgent']['status'])}}@endif</mark></div>
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
                    <td class="col-xs-1">退款信息</td>
                    <td class="col-xs-2">评价</td>
                </th>

                @foreach($data['goods'] as $goods)
                    <tr class="row">
                        <td><img src="{{$goods['pic_url']}}" style="width:100px" alt=""></td>
                        <td>{{$goods['name']}}</td>
                        <td>{{$goods['pivot']['sku']}}</td>
                        <td>{{$goods['pivot']['product_count']}}</td>
                        <td>{{$goods['pivot']['product_price']}}</td>
                        <td>{{$goods['pivot']['product_price'] * $goods['pivot']['product_count']}}</td>
                        <td>@if ($goods['pivot']['refund_product_count'] > 0)数量：{{$goods['pivot']['refund_product_count']}} <br> 金额：{{$goods['pivot']['refund_total_amount']}}@endif</td>
                        <td>@if ($goods['pivot']['score'] > 0)<span class="label label-primary">{{$goods['pivot']['score']}}分</span> &nbsp;&nbsp;{{$goods['pivot']['comment']}}@endif</td>
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
                <tr><td class="col-xs-1">收货地址：</td><td>{{$data['address']['province']}} {{$data['address']['city']}} {{$data['address']['county']}} {{$data['address']['detail_info']}}</td></tr>
                <tr><td class="col-xs-1">收件人：</td><td>{{$data['address']['name']}}</td></tr>
                <tr><td class="col-xs-1">手机：</td><td>{{$data['address']['phone']}}</td></tr>
                <tr><td class="col-xs-1">邮编：</td><td>{{$data['address']['postal_code']}}</td></tr>
                <tr><td class="col-xs-1">快递公司：</td><td>{{$data['address']['delivery_company']}}</td></tr>
                <tr><td class="col-xs-1">快递单号：</td><td>{{$data['address']['delivery_number']}}</td></tr>
                <tr>
                    <td class="col-xs-1">备注：</td>
                    <td>
                        @foreach($data['goods'] as $goods)
                            {{$goods['name']}}&nbsp;&nbsp;{{str_replace(['口味:','规格:','重量:'], '', $goods['pivot']['sku'])}} * {{$goods['pivot']['product_count']}};<br>
                        @endforeach
                    </td>
                </tr>
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
                    <td class="col-xs-3">操作时间</td>
                <td class="col-xs-3">操作类型</td>
                    <td class="col-xs-3">操作事件</td>
                    <td class="col-xs-3">备注</td>
                </th>
                @foreach($eventLogs as $log)
                    <tr class="row">
                        <td>{{$log['created_at']}}</td>
                        <td>{{App\Models\Order::getEventType($log['event_type'])}}</td>
                        @if($log['event_type'] == App\Models\Order::EVENT_TYPE_REFUND)
                            <td>{{App\Models\Order::getRefundEvents($log['event'])}}</td>
                            @else
                            <td>{{App\Models\Order::getStatus($log['event'])}}</td>
                        @endif
                        <td>{{$log['remark']}}</td>
                    </tr>
                @endforeach
            </table>

        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<script>

    let headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };

    async function refund()
    {
        const { value: formValues } = await Swal.fire({
            title: '订单退款',
            html:
                '<h5 class="text-left">请选择退款类型</h5>' +
                '<select id="refund_type" class="swal2-input">' +
                '<option value="">请选择</option>' +
                '<option value="1">全部退款</option>' +
                '<option value="2">运费退款</option>' +
                '<option value="3">部分商品退款</option>' +
                '</select>' +
                '<h5 class="text-left">备注</h5>' +
                '<input id="refund_desc" class="swal2-input" placeholder="同意退款">',
            focusConfirm: false,
            preConfirm: () => {
                let refund_type = document.getElementById('refund_type').value;
                let refund_desc = document.getElementById('refund_desc').value;
                if (!refund_type) {
                    swal.showValidationError('请选择退款类型');
                    return
                }
                if (!refund_desc) {
                    refund_desc = '同意退款';
                    return
                }
                return {
                    'order_no' : "{{$data['order_no']}}",
                    'refund_type' : refund_type,
                    'refund_desc' : refund_desc
                }
            }
        })
        if (formValues) {
            console.log(formValues)
            if (formValues.refund_type == 3) {
                selectGoods();
            } else {
                refundApi()
            }
        }
    }

    async function selectGoods()
    {
        const { value: formValues } = await Swal.fire({
            title: '订单退款',
            html:
                '<h5 class="text-left">请选择退款类型</h5>' +
                '<select id="refund_type" class="swal2-input">' +
                '<option value="">请选择</option>' +
                '<option value="1">全部退款</option>' +
                '<option value="2">运费退款</option>' +
                '<option value="3">部分商品退款</option>' +
                '</select>' +
                '<h5 class="text-left">备注</h5>' +
                '<input id="refund_desc" class="swal2-input" placeholder="同意退款">',
            focusConfirm: false,
            preConfirm: () => {
                let refund_type = document.getElementById('refund_type').value;
                let refund_desc = document.getElementById('refund_desc').value;
                if (!refund_type) {
                    swal.showValidationError('请选择退款类型');
                    return
                }
                if (!refund_desc) {
                    refund_desc = '同意退款';
                    return
                }
                return {
                    'order_no' : "{{$data['order_no']}}",
                    'refund_type' : refund_type,
                    'refund_desc' : refund_desc
                }
            }
        })
        if (formValues) {
            console.log(formValues)
            if (formValues.refund_type == 3) {
            } else {
                refundApi()
            }
        }
    }

    function refundApi(formValues)
    {
        Swal.fire({
            title: '确认退款?',
            text: "确认退款后，退款金额会原路退回!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '确认',
            cancelButtonText: '取消'
        }).then((result) => {
            if (result.value) {
                Swal.fire(123);return
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: "{{route('admin.order.refund')}}", // Invalid URL on purpose
                        type: 'POST',
                        headers: headers,
                        data: JSON.stringify(formValues)
                    })
                        .done(function(data) {
                            if (data.code == 0) {
                                Swal.fire('操作成功').then(function(){
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    'title': '失败',
                                    'text': data.msg,
                                    'type': 'fail'
                                }).then(function(){
                                    location.reload();
                                });
                            }
                            resolve(data)
                        })
                        .fail(function(error) {
                            reject(error)
                        });
                })
            }
        })
    }

    function updateOrderStatus(title, text, type)
    {
        Swal.fire({
            title: title,
            text: text,
            type:'question',
            showCancelButton: true
        }).then((result) => {
            if (typeof result.dismiss === 'undefined') {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url: "{{route('admin.order.update-status')}}", // Invalid URL on purpose
                        type: 'POST',
                        headers: headers,
                        data: JSON.stringify({
                            id: "{{$data['id']}}",
                            type: type
                        })
                    })
                    .done(function(data) {
                        if (data.code == 0) {
                            Swal.fire('操作成功').then(function(){
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                'title': '失败',
                                'text': data.msg,
                                'type': 'fail'
                            }).then(function(){
                                location.reload();
                            });
                        }
                        resolve(data)
                    })
                    .fail(function(error) {
                        reject(error)
                    });
                })
            }
        }).catch((error) => {
            Swal.fire(`错误：${error.status}`,function () {
                location.reload()
            });
        });
    }

    async function delivery(title, text)
    {

        const { value: formValues } = await Swal.fire({
            title: '发货',
            text: '请填写发货信息',
            html:
                '<h5 class="text-left">快递公司</h5>' +
                '<select id="company" class="swal2-input">' +
                    '<option value="顺丰快递">顺丰快递</option>' +
                    '<option value="京东快递">京东快递</option>' +
                    '<option value="韵达快递">韵达快递</option>' +
                '</select>' +
                '<h5 class="text-left">快递单号</h5>' +
                '<input id="number" class="swal2-input">',
            focusConfirm: false,
            preConfirm: () => {
                let company = document.getElementById('company').value;
                let number = document.getElementById('number').value;
                if (!number) {
                    swal.showValidationError('请输入快递单号');
                    return
                }
                return {
                    'id' : "{{$data['id']}}",
                    'delivery_company' : company,
                    'delivery_number' : number
                }
            }
        })

        if (formValues) {
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: "{{route('admin.order.delivery')}}", // Invalid URL on purpose
                    type: 'POST',
                    headers: headers,
                    data: JSON.stringify(formValues)
                })
                    .done(function(data) {
                        if (data.code == 0) {
                            Swal.fire('操作成功').then(function(){
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                'title': '失败',
                                'text': data.msg,
                                'type': 'fail'
                            }).then(function(){
                                location.reload();
                            });
                        }
                        resolve(data)
                    })
                    .fail(function(error) {
                        reject(error)
                    });
            })
        }
    }
</script>
