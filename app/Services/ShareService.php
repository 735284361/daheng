<?php

namespace App\Services;

use App\Models\Goods;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ShareService
{


    public static function getGoodsImageMaker($goods, $user, $xcxurl) {
        // 背景
        $background = [
            base_path().'/public/static/share/goods_share_bg1.png',
            base_path().'/public/static/share/goods_share_bg1.png',
            base_path().'/public/static/share/goods_share_bg1.png'
        ];

        // 字体
        $font_paths =  [
            base_path().'/public/static/font/gangbi0.ttf',
            base_path().'/public/static/font/gangbi1.ttf'
        ];
        $font_path = $font_paths[rand(0, 1)];
        $img = Image::make($background[rand(0,2)])->resize(640, 1000);

        $face_img = Image::make($user['avatar'])
            ->resize(60, 60);
        // 头部加头像
        $img->insert(
            $face_img,
            'top-left',
            55,
            76
        );

        $nickname = self::filterEmoji($user['nickname']);
        // 头部加昵称
        $img->text($nickname.'为你推荐', 131, 120, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(32);
            $font->valign('bottom');
            $font->color('#333333');
        });

        // 商品图片区域
        $bodyimage = Image::canvas(533, 475, '#eee');

        $pic = getStorageUrl($goods['pics'][0]);
        $goodsimage = Image::make($pic)
            ->resize(531, 354);

        $bodyimage->insert($goodsimage, 'top-left', 1, 1);

        $bodybuttomimage = Image::canvas(531, 164, '#fff');

        $strings =  self::mbStrSplit($goods['name'], 18);

        $i = 0; //top position of string
        if (count($strings) == 1) {
            $bodybuttomimage->text($strings[0], 17, 44, function ($font) use ($font_path) {
                $font->file($font_path);
                $font->size(30);
                $font->valign('top');
                $font->color('#333333');
            });
        } else {
            foreach($strings as $key => $string) {
                if ($key >= 2) {
                    break;
                }
                // 标题部分
                $bodybuttomimage->text($string, 17, 16 + $i, function ($font) use ($font_path) {
                    $font->file($font_path);
                    $font->size(27);
                    $font->valign('top');
                    $font->color('#333333');
                });
                $i = $i + 43; //shift top postition down 42
            }
        }

        // 价格
        if ($goods['line_price']) {
            $price = $goods['line_price'];
        } else {
            $price = $goods['price'];
        }
        $bodybuttomimage->text('原价：'.$price, 17, 118, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(24);
            $font->valign('top');
            $font->color('#a3a3a3');
        });

        if ($goods['group'] && $goods['group']['endtime'] > date("Y-m-d H:i:s")) {
            $xianjiaString = '团购价：';
            $xianPrice = $goods['group']['price'];

            $tuanButton = Image::canvas(107, 33, '#ff0000');

            $tuanButton->text($goods['group']['min_quantity'].'人团', 22, 6, function ($font) use ($font_path) {
                $font->file($font_path);
                $font->size(25);
                $font->align('left');
                $font->valign('top');
                $font->color('#fff');
            });

            $bodybuttomimage->insert($tuanButton, 'top-right', 30, 110);
        } else {
            $xianjiaString = '现价：';
            $xianPrice = $goods['price'];
        }

        $bodybuttomimage->text($xianjiaString, 180, 118, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(24);
            $font->valign('top');
            $font->color('#333333');
        });

        $bodybuttomimage->text('￥'.$xianPrice, 270, 118, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(27);
            $font->valign('top');
            $font->color('#fe0000');
        });
        $bodyimage->insert($bodybuttomimage, 'top-left', 1, 310);
        $img->insert($bodyimage, 'top-left', 55, 154);

        // 底部二维码部分
        $dibuimage = Image::canvas(596,308);

        $codeimage = Image::canvas(255,255,'#eee');
        $codesourceimage = Image::make($xcxurl)
            ->resize(249, 249);
        $codeimage->insert($codesourceimage, 'top-left', 3, 3);

        $dibuimage->insert($codeimage, 'top-left', 33, 23);

        $dibuimage->text('长按识别小程序码', 300, 110, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(27);
            $font->valign('top');
            $font->color('#333333');
        });

        $dibuimage->text('立即抢购！', 370, 150, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(27);
            $font->valign('top');
            $font->color('#333333');
        });

        $img->insert($dibuimage, 'top-left', 22, 650);

        return $img;
    }

    public static function getAgentCode($user, $xcxurl) {
        // 背景
        $background = [
            base_path().'/public/static/share/goods_share_bg1.png',
            base_path().'/public/static/share/goods_share_bg1.png',
            base_path().'/public/static/share/goods_share_bg1.png'
        ];

        // 字体
        $font_paths =  [
            base_path().'/public/static/font/gangbi0.ttf',
            base_path().'/public/static/font/gangbi1.ttf'
        ];

        // 商品宣传
        $intro_path = base_path().'/public/static/share/agent_share_intro.jpeg';

        $font_path = $font_paths[rand(0, 1)];
        $img = Image::make($background[rand(0,2)])->resize(640, 1000);

        $face_img = Image::make($user['avatar'])
            ->resize(60, 60);
        // 头部加头像
        $img->insert(
            $face_img,
            'top-left',
            55,
            76
        );

        // 商品图片区域
        $bodyimage = Image::canvas(533, 475, '#eee');

        $goodsimage = Image::make($intro_path)
            ->resize(530, 472);

        $bodyimage->insert($goodsimage, 'top-left', 1, 1);

        $img->insert($bodyimage, 'top-left', 55, 154);

        $nickname = self::filterEmoji($user['nickname']);
        // 头部加昵称
        $img->text($nickname.'向您推荐好物', 131, 120, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(32);
            $font->valign('bottom');
            $font->color('#333333');
        });

        // 底部二维码部分
        $dibuimage = Image::canvas(596,308);

        $codeimage = Image::canvas(253,253,'#eee');
        $codesourceimage = Image::make($xcxurl)
            ->resize(249, 249);
        $codeimage->insert($codesourceimage, 'top-left', 2, 2);

        $dibuimage->insert($codeimage, 'top-left', 33, 23);

        $dibuimage->text('长按识别小程序码', 300, 110, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(27);
            $font->valign('top');
            $font->color('#333333');
        });

        $dibuimage->text('立即围观！', 370, 150, function ($font) use ($font_path) {
            $font->file($font_path);
            $font->size(27);
            $font->valign('top');
            $font->color('#333333');
        });

        $img->insert($dibuimage, 'top-left', 22, 650);
        $saveName = uniqid().'.png';

        $path = storage_path('app/public/qrcode/').$saveName;
        $img->save($path);

        return asset('storage/qrcode/'.$saveName);

//        return $img;
    }

    /**
     * 字符截取
     * @param $str
     * @param $len
     * @return array
     */
    public static function mbStrSplit($str, $len)
    {
        $strLen = Str::length($str);
        $count = ceil($strLen / $len);
        $arr = [];
        for ($i = 0; $i < $count; $i++) {
            $arr[] =  mb_substr($str, $i * $len, $len, 'utf8');
        }
        return $arr;
    }

    /**
     * 表情过滤
     * @param $str
     * @return string
     */
    public static function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return trim($str);
    }

    public static function getGoodsShareQrCode($id, $uid)
    {
        $goods = Goods::find($id);
        if ($goods) {
            $app = \EasyWeChat::miniProgram();
            $response = $app->app_code->get('/pages/goods-details/index?id='.$id.'&user_id='.$uid);
            $path = storage_path('app/public/share/goods_qrcode/');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'share/goods_qrcode/'.$filename;
            return storage_path('app/public/'.$qrcode);
        }
        return false;
    }

}
