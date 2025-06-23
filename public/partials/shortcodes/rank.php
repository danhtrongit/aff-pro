<?php 
    if($ranks):
    ?>
        <style>
            .ranks{
                border: 1px solid #ea4c13;
                border-radius: 6px;
        /*      padding: 20px;*/
/*                max-width: 600px;*/
                position: relative;
                margin-bottom: 15px;
            }
            .ds-banner-top {
                width: 315px;
                position: absolute;
                top: -31px;
                left: 0;
                right: 0;
                margin-left: auto;
                margin-right: auto;
                z-index: 1;
            }
            .ds-title-banner {
                position: absolute;
                top: 30px;
                left: 0;
                right: 0;
                margin-left: auto;
                margin-right: auto;
                color: #fff;
                overflow: hidden;
                background: #ea4c13;
                font-size: 20px;
                line-height: 35px;
                text-align: center;
                text-transform: uppercase;
                border-radius: 0 0 16px 16px;
            }
             .ds-header-top {
                display: flex;
                padding-bottom: 12px;
                margin-top: 30px;
            }
            .ds-rank-top {
                width: 10%;
                min-width: 35px;
                display: flex;
                justify-content: center;
                align-items: center;
                position: relative;
                padding-left: 24px;
            }
            .ds-name-top {
                width: 60%;
                text-align: center;
                display: flex;
                margin-left: 32px;
                align-items: center;
            }
            .ds-total-ear-top, .ds-home-top-chart .ds-table-top .ds-win-tour-top {
                width: 30%;
                text-align: center;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .ds-rank-top.ds-top-one {
                padding-top: 8px;
                padding-bottom: 4px;
                background: linear-gradient(90deg,rgba(247,80,20,.3),rgba(250,165,20,0) 100%,rgba(247,80,20,0) 0);
            }
            .ds-rank-top {
                width: 10%;
                min-width: 35px;
                display: flex;
                justify-content: center;
                align-items: center;
                position: relative;
                padding-left: 24px;
            }
            .ds-line-player {
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 4px;
                background: #f75014;
            }
            .ds-item-body{
                margin-bottom: 12px;
                display: flex;
            }
            .wrap-name{
                display: flex;
                align-items: center;
            }
            .wrap-name img{
                margin-right: 4px;
                border-radius: 50%;
                
            }
            .ranks-content{
                margin-top: 50px;
            }

        </style>

        <div class="ranks">
            <div class="ranks-top">
                <div class="ds-banner-top">
                    
                    <div class="ds-title-banner"><?php echo $atts['title'] ?></div>
                </div>

            </div>
            <div class="ranks-content">
                <div class="ds-header-top">
                    <div class="ds-rank-top text-desc-regular"><span class="text-desc-regular clr-text-lv1">Hạng</span></div>
                    <div class="ds-name-top"><span class="ds-title-name">Tên</span></div>
                    <div class="ds-total-ear-top"><span class="text-desc-regular clr-text-lv1"><?php echo $atts['type'] == 'order' ? 'Số đơn hàng' : 'Doanh thu' ?> <?php echo $atts['range'] == 'year' ? date('Y') : 'tháng ' .date('m-Y') ?></span></div>
                </div>
                <?php foreach ($ranks as $key => $rank): $avatar = get_avatar_url($rank['user_id']) ?>
                    <?php if ($key == 0): ?>
                    <div class="ds-item-body ds-background-lv3">
                        <div class="ds-rank-top ds-top-one">
                            <img alt="Rank" src="<?php echo AFF_URL ?>/public/images/rank-top1.png" style="width: 35px;">
                        </div>
                        <div class="ds-name-top">
                            <div class="wrap-name">
                                <img src="<?php echo $avatar ? $avatar : AFF_URL ?>/public/images/user.svg" style="width: 25px;">
                                <?php echo $rank['user_login']?>
                            </div>
                        </div>
                        <div class="ds-total-ear-top">
                            <span class="text-desc-semi-bold clr-text-lv1 ds-top-one"><?php echo number_format($rank['total'])?> <?php echo $atts['type'] == 'order' ? '' : 'đ' ?></span>
                        </div>
                    </div>
                    <?php elseif($key == 1): ?>
                    <div class="ds-item-body">
                        <div class="ds-rank-top ds-top-two">
                            <img alt="Rank" src="<?php echo AFF_URL ?>/public/images/rank-top2.png" style="width: 30px;">
                        </div>
                        <div class="ds-name-top">
                            <div class="wrap-name">
                                <img src="<?php echo $avatar ? $avatar : AFF_URL ?>/public/images/user.svg" style="width: 25px;">
                                <?php echo $rank['user_login']?>
                            </div>
                        </div>
                        <div class="ds-total-ear-top">
                            <span class="text-desc-semi-bold clr-text-lv1 ds-top-one"><?php echo number_format($rank['total'])?> <?php echo $atts['type'] == 'order' ? '' : 'đ' ?></span>
                        </div>
                    </div>
                    <?php elseif($key == 2): ?>
                    <div class="ds-item-body">
                        <div class="ds-rank-top ds-top-three">
                            <img alt="Rank" src="<?php echo AFF_URL ?>/public/images/rank-top3.png" style="width: 25px;">
                        </div>
                        <div class="ds-name-top">
                            <div class="wrap-name">
                                <img src="<?php echo $avatar ? $avatar : AFF_URL ?>/public/images/user.svg" style="width: 25px;">
                                <?php echo $rank['user_login']?>
                            </div>
                        </div>
                        <div class="ds-total-ear-top">
                            <span class="text-desc-semi-bold clr-text-lv1 ds-top-one"><?php echo number_format($rank['total'])?> <?php echo $atts['type'] == 'order' ? '' : 'đ' ?></span>
                        </div>
                    </div>

                    <?php else: ?>
                    <div class="ds-item-body">
                        <div class="ds-rank-top ds-top-three">
                            <b><?php echo $key + 1?></b>
                        </div>
                        <div class="ds-name-top">
                            <div class="wrap-name">
                                <img src="<?php echo $avatar ? $avatar : AFF_URL ?>/public/images/user.svg" style="width: 25px;">
                                <?php echo $rank['user_login']?>
                            </div>
                        </div>
                        <div class="ds-total-ear-top">
                            <span class="text-desc-semi-bold clr-text-lv1 ds-top-one"><?php echo number_format($rank['total'])?> <?php echo $atts['type'] == 'order' ? '' : 'đ' ?></span>
                        </div>
                    </div>
                    <?php endif ?>
                <?php endforeach ?>

            </div>
        </div>
        <?php
        endif;

?>