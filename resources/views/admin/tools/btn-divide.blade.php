<div class="btn-group" data-toggle="buttons">
    <a class='report-posts btn btn-sm btn-success' onclick="divide()">{{$month}}月度奖金结算</a>
</div>

<script>
    let headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };

    function divide()
    {
        Swal.fire({
            title: '确认进行分成?',
            text: "确认进行{{$month}} 分成!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '确认',
            cancelButtonText: '取消'
        }).then((result) => {
            Swal.showLoading()
            debugger
            if (result.value) {
                $.ajax({
                    url: "{{route('admin.divide.divide')}}", // Invalid URL on purpose
                    type: 'GET',
                    headers: headers
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
                                'type': 'success'
                            }).then(function(){
                                location.reload();
                            });
                        }
                        resolve(data)
                    })
                    .fail(function(error) {
                        reject(error)
                    })
                    .complete(function() {
                        Swal.hideLoading()
                    });
            }
        })



    }

</script>
