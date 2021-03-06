  <?php

session_cache_limiter('private_no_expire');
session_start();

require_once('general_user_log_status.php');


/*検索キーワードを代入*/
$book_search_key = '%'.$_GET['keyword'].'%';



        
/*DB接続*/    
    require_once('db_connect.php');
    

/*SQL*/           
     try{
         $sql2 = 
             
         "SELECT 
         
         book_title, author, price, isbn_10, img, publish_date
         
         FROM book
         
         WHERE book_title LIKE :book_title OR author LIKE :author
         
         ORDER BY price ASC
         
         LIMIT 8"; 
        
            $stmt = $pdo->prepare($sql2);
            $stmt->bindValue(':book_title', $book_search_key, PDO::PARAM_STR);
            $stmt->bindValue(':author', $book_search_key, PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->rowCount();
            print '検索結果は'.$count.'件です。';
         
     }catch(PDOException $r){
        print "エラー".$r->getMessage();
    }
        
        

        
    
?>



<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="/css/search_result.css">
    <link rel="stylesheet" href="/css/reset.css">
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC434MBbhe6MuEUVmTwJsCnp-jwL7grBYI&callback=initMap" async defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script type='text/javascript'></script>
    
    <script>
        $(document).ready(function(){
            
/*駅名を入力して検索のためのオートコンプリート*/   
          $(".station_name").keyup(function(e){
            e.preventDefault();
            var search_val = $(".station_name").val();
            $.post("autocomp_for_sta.php", {station_name : search_val}, function(data){
              if(data.length>0){
                $(".station_name").autocomplete({
                  source: data
                });
              }
            })
          });
            
            
            
/*SELECT BOXのためのコード*/
            
            
  // プルダウン変更時に遷移
          $('select[name=pulldown1]').change(function() {
            if ($(this).val() != '') {
              window.location.href = $(this).val();
            }
          });
            
            
        });
        
        /*以下、GeoLocation APIにて現在地情報を取得*/
//1．位置情報の取得に成功した時の処理
function mapsInit(position) {
  try {
    //lat=緯度、lon=経度 を取得
    var lat = position.coords.latitude;
    var lon = position.coords.longitude;
//    alert("位置情報取得完了！");
    $('.lat').val(lat);
    $('.lon').val(lon);
      

  } catch (error) {
    console.log("getGeolocation: " + error);
  }
};
       
       

//2． 位置情報の取得に失敗した場合の処理
function mapsError(error) {
  var e = "";
  if (error.code == 1) { //1＝位置情報取得が許可されてない（ブラウザの設定）
    e = "位置情報が許可されてません";
  }
  if (error.code == 2) { //2＝現在地を特定できない
    e = "現在位置を特定できません";
  }
  if (error.code == 3) { //3＝位置情報を取得する前にタイムアウトになった場合
    e = "位置情報を取得する前にタイムアウトになりました";
  }
  alert("エラー：" + e);
};

//3.位置情報取得オプション
var set ={
  enableHighAccuracy: true, //より高精度な位置を求める
  maximumAge: 20000,        //最後の現在地情報取得が20秒以内であればその情報を再利用する設定
  timeout: 10000            //10秒以内に現在地情報を取得できなければ、処理を終了
};

//Main:位置情報を取得する処理 //getCurrentPosition :or: watchPosition
function initMap(){

  navigator.geolocation.getCurrentPosition(mapsInit, mapsError, set);
}

</script>
</head>
<body>

　  <select name="pulldown1">
        <option value="">並べ替え</option>
        <option value="book_select_keyword.php?keyword=<?= $book_search_key ?>">キーワード関連順</option>
        <option value="book_select_date_desc.php?keyword=<?= $book_search_key ?>">発売日が新しい順</option>
        <option value="book_select_date_asc.php?keyword=<?= $book_search_key ?>">発売日が古い順</option>
        <option value="book_select_price_cheap.php?keyword=<?= $book_search_key ?>">価格が安い順</option>
        <option value="book_select_price_high.php?keyword=<?= $book_search_key ?>">価格が高い順</option>
    </select>
 
   
   
   <table>
       
      
<?php
        
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){            
           
?>
       
       <tr>
       
           <td>
              <div id="list_wrap">
               <div id="left">
                   <ul>

                       <li><img src="/doc/<?= e($row['img']) ?>" width="160" height="160"></li>
                    
                   </ul>
               </div>
               
               <div id="right">
                  <ul>
                     
                      <li>タイトル: <?= e($row['book_title']) ?></li>
                      <li>著者:   <a href="author.php?author=<?= e($row['author']) ?>"><?= e($row['author']) ?></a></li>
                      <li>発売日:   <?= e($row['publish_date']) ?></li>
                      <li>価格:   <?= e($row['price']) ?> 円（税込）</li>
                      <li>
                             <form action="geo2.php" method="post">
                              <input type="submit" value="現在地から距離順で書店を検索">
                              <input type="hidden" name="book_title" value="<?= e($row['book_title']) ?>">
                              <input type="hidden" name="lat" class="lat">
                              <input type="hidden" name="lon" class="lon">
                              <input type="hidden" name="author" value="<?= e($row['author']) ?>">
                              <input type="hidden" name="publish_date" value="<?= e($row['publish_date']) ?>">
                              <input type="hidden" name="price" value="<?= e($row['price']) ?>">
                              <input type="hidden" name="img" value="<?= e($row['img']) ?>">
                          </form>
                      </li> 
                      

                      <li>
                          <form action="sta2.php" method="post">
                              <input type="text" name="station_name" class="station_name">
                              <input type="submit" value="駅名から検索" placeholder="駅名を入力">
                              <input type="hidden" name="book_title" value="<?= e($row['book_title']) ?>">
                              <input type="hidden" name="author" value="<?= e($row['author']) ?>">
                              <input type="hidden" name="publish_date" value="<?= e($row['publish_date']) ?>">
                              <input type="hidden" name="price" value="<?= e($row['price']) ?>">
                              <input type="hidden" name="img" value="<?= e($row['img']) ?>">
                          </form>
                      </li>
                  </ul>
                   
               </div>
            </div>
           </td>

       </tr>

       
       
   </table>
   
<?php
                
        }
    
?>
    
</body>
</html>
