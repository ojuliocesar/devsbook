<div class="box feed-item">
    <div class="box-body">
        <div class="feed-item-head row mt-20 m-width-20">
            <div class="feed-item-head-photo">
                <a href=""><img src="<?= $base ?>/media/avatars/<?= $data->user->avatar ?>"/></a>
            </div>
            <div class="feed-item-head-info">
                <a href=""><span class="fidi-name"><?= $data->user->name ?></span></a>
                <span class="fidi-action"><?php echo $data->type == 'text' ?
                    'fez um post':
                    'Postou uma foto' 
                ?></span>
                <br/>
                <span class="fidi-date"><?= date('d/m/y', strtotime($data->created_at)) ?></span>
            </div>
            <div class="feed-item-head-btn">
                <img src="<?= $base ?>/assets/images/more.png" />
            </div>
        </div>
        <div class="feed-item-body mt-10 m-width-20">
            <?= nl2br($data->body) ?>
        </div>
        <div class="feed-item-buttons row mt-20 m-width-20">
            <div class="like-btn <?= $data->liked ? 'on' : '' ?>"><?= $data->likeCount ?></div>
            <div class="msg-btn"><?= count($data->comments) ?></div>
        </div>
        <div class="feed-item-comments">

            <!-- <div class="fic-item row m-height-10 m-width-20">
                <div class="fic-item-photo">
                    <a href=""><img src="<?= $base ?>/media/avatars/default.jpg" /></a>
                </div>
                <div class="fic-item-info">
                    <a href="">Bonieky Lacerda</a>
                    Muito legal, parabéns!
                </div> -->
            <!-- </div> -->

            <div class="fic-answer row m-height-10 m-width-20">
                <div class="fic-item-photo">
                    <a href=""><img src="<?= $base ?>/media/avatars/<?= $loggedUser->avatar ?>" /></a>
                </div>
                <input type="text" class="fic-item-field" placeholder="Escreva um comentário" />
            </div>

        </div>
    </div>
</div>