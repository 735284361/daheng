
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">操作</h3>
    </div>

    <div class="box-body">
        <div class="container">
            <div class="col-md-12">
                @if ($data['status'] == App\Models\Agent::STATUS_APPLY)
                    <button class="btn btn-success" onclick="updateAgentStatus('设置为审核通过？', '即将设置为审核通过，通过后该用户即成为代理商?', '{{App\Models\Agent::STATUS_NORMAL}}')">设为通过</button>
                    <button class="btn btn-warning" onclick="updateAgentStatus('设置为未通过？', '即将设置为审核未通过，则该用户不能成为代理商?', '{{App\Models\Agent::STATUS_REFUSE}}')">拒绝申请</button>
                @elseif ($data['status'] == App\Models\Agent::STATUS_NORMAL)
                    <button class="btn btn-warning" onclick="updateAgentStatus('设置为禁用？', '即将修改该代理商为禁用，禁用后该用户将不能进行代理分成，是否继续？', '{{App\Models\Agent::STATUS_DISABLE}}')">设为禁用</button>
                @elseif ($data['status'] == App\Models\Agent::STATUS_DISABLE)
                    <button class="btn btn-success" onclick="updateAgentStatus('设置为正常？', '即将修改该代理商为正常状态，修改后该代理商能正常进行相关代理分成，是否继续？', '{{App\Models\Agent::STATUS_NORMAL}}')">设为正常</button>
                @endif
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">代理商信息</h3>
    </div>

    <div class="box-body">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户昵称：{{$data['user']['nickname']}}</div>
            <div class="col-md-3">用户电话：{{$data['user']['phone']}}</div>
            <div class="col-md-3">用户邮箱：{{$data['user']['email']}}</div>
        </div>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-3">用户状态：<mark>{{App\Models\Agent::getStatus($data['status'])}}</mark></div>
            <div class="col-md-3">创建时间：{{$data['created_at']}}</div>
            <div class="col-md-3">更新时间：{{$data['updated_at']}}</div>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
</div>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">顾客</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td class="col-xs-2">头像</td>
                    <td class="col-xs-2">成员昵称</td>
                    <td class="col-xs-2">用户编号</td>
                    <td class="col-xs-2">消费金额</td>
                    <td class="col-xs-2">订单数量</td>
                    <td class="col-xs-2">创建时间</td>
                </th>

                @foreach($data['members'] as $member)
                    <tr class="row">
                        <td><img src="{{$member['user']['avatar']}}" style="width: 30px" alt=""></td>
                        <td>{{$member['user']['nickname']}}</td>
                        <td>{{$member['user']['id']}}</td>
                        <td>{{$member['amount']}}</td>
                        <td>{{$member['order_number']}}</td>
                        <td>{{$member['created_at']}}</td>
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
        <h3 class="box-title">代理订单</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td>成员昵称</td>
                    <td>订单编号</td>
                    <td>订单状态</td>
                    <td>每单分成金额</td>
                    <td>每单分成状态</td>
                    <td>月度奖金分成状态</td>
                    <td>订单日期</td>
                </th>

                @foreach($data['agentMembersOrders'] as $order)
                    <tr class="row">
                        <td>{{$order['order']['user']['nickname']}}</td>
                        <td>{{$order['order_no']}}</td>
                        <td>{{App\Models\Order::getStatus($order['order']['status'])}}</td>
                        <td>{{$order['commission']}}</td>
                        <td>{{App\Models\AgentOrderMaps::getStatus($order['status'])}}</td>
                        <td>{{App\Models\AgentOrderMaps::getDivideStatus($order['status_divide'])}}</td>
                        <td>{{$order['created_at']}}</td>
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

    function updateAgentStatus(title, text, status)
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
                        url: "{{route('admin.agent.update-status')}}", // Invalid URL on purpose
                        type: 'POST',
                        headers: headers,
                        data: JSON.stringify({
                            id: "{{$data['id']}}",
                            status: status
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

</script>
