<?php
class Privacy {
  const PRIVATE_ACCESS = 1;
  const FRIEND_ACCESS = 2;
  const FOF_ACCESS = 4;
  const EXT_ACCESS = 8;
  const PUBLIC_ACCESS = 16;
  public static function lookup() {
    return array(
        array(
            'key'=>self::PUBLIC_ACCESS,
            'value'=>__('privacy.public')
        ),
        array(
            'key'=>self::FRIEND_ACCESS,
            'value'=>__('privacy.friends')
        ),
        array(
            'key'=>self::PRIVATE_ACCESS,
            'value'=>__('privacy.private')
        )
    );
  }
}
?>