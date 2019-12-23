<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title float-right">申请详情</h3>
    </div>

    <div class="box-body">
        <div class="container">
            <div class="col-md-12"><small>用户编号：</small><em>{{$data['user_id']}}</em></div>
            <div class="col-md-12"><small>用户昵称：</small><em>{{$data['user']['nickname']}}</em></div>
            <div class="col-md-12"><small>订单编号：</small><em>{{$data['withdraw_order']}}</em></div>
            <div class="col-md-12"><small>申请金额：</small><em>{{$data['apply_total']}}</em></div>
            <div class="col-md-12"><small>申请状态：</small><em><mark>{{App\Models\Withdraw::getStatus($data['status'])}}</mark></em></div>
            <div class="col-md-12"><small>申请时间：</small><em>{{$data['created_at']}}</em></div>
        </div>
    </div>

    <div class="box-header with-border">
        <h3 class="box-title">日志</h3>
    </div>

    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <th class="row">
                    <td class="col-xs-4">时间</td>
                    <td class="col-xs-4">事件</td>
                    <td class="col-xs-4">备注</td>
                </th>

                @foreach($data['logs'] as $log)
                    <tr class="row">
                        <td>{{$log['created_at']}}</td>
                        <td>{{$log['status']}}</td>
                        <td>{{$log['remark']}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->

    @if($data['status'] == App\Models\Withdraw::STATUS_APPLY)
        <div class="box-body">
            <div class="box-header with-border">
                <h3 class="box-title">操作</h3>
            </div>
            <br>
            <div class="container">
                <div class="col-md-12">
                    <button class="btn btn-success" onclick="agree()">同意</button>
                    <button class="btn btn-danger" onclick="cancel()">拒绝</button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    let headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }

    async function agree()
    {
        const { value: formValues, dismiss } = await Swal.fire({
            title: '同意打款？',
            text: '同意打款后，将直接打款至用户微信账户',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: '确认打款',
            cancelButtonText: '取消',
            showLoaderOnConfirm: true,
            inputPlaceholder: '备注',
        })
        if (!dismiss) {
            let values = {
                'id' : "{{$data['id']}}",
                'remark' : formValues,
            }
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: "{{route('admin.withdraw.agree')}}", // Invalid URL on purpose
                    type: 'POST',
                    headers: headers,
                    data: JSON.stringify(values)
                })
                    .done(function(data) {
                        if (data.code == 0) {
                            Swal.fire('操作成功',function () {
                                location.reload()
                            }).then(function () {
                                location.reload()
                            });
                        } else {
                            Swal.fire({
                                'title': '失败',
                                'text': data.msg,
                                'type': 'fail'
                            }).then(function () {
                                location.reload()
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

    async function cancel()
    {
        const { value: formValues, dismiss } = await Swal.fire({
            title: '拒绝申请',
            text: '请填写拒绝理由',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: '确认拒绝',
            cancelButtonText: '取消',
            showLoaderOnConfirm: true,
            inputPlaceholder: '拒绝理由'
        })
        if (!dismiss) {
            let values = {
                'id' : "{{$data['id']}}",
                'remark' : formValues,
            }
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: "{{route('admin.withdraw.refuse')}}", // Invalid URL on purpose
                    type: 'POST',
                    headers: headers,
                    data: JSON.stringify(values)
                })
                    .done(function(data) {
                        if (data.code == 0) {
                            Swal.fire('操作成功').then(function () {
                                location.reload()
                            });
                        } else {
                            Swal.fire({
                                'title': '失败',
                                'text': data.msg,
                                'type': 'fail'
                            }).then(function () {
                                location.reload()
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
