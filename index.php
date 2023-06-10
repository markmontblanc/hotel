<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   setcookie('user_id', create_unique_id(), time() + 60*60*24*30, '/');
   header('location:index.php');
}

if(isset($_POST['check'])){

   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);

   $total_rooms = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_rooms += $fetch_bookings['rooms'];
   }

   // if the hotel has total 30 rooms 
   if($total_rooms >= 30){
      $warning_msg[] = 'rooms are not available';
   }else{
      $success_msg[] = 'rooms are available';
   }

}

if(isset($_POST['book'])){

   $booking_id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $rooms = $_POST['rooms'];
   $rooms = filter_var($rooms, FILTER_SANITIZE_STRING);
   $check_in = $_POST['check_in'];
   $check_in = filter_var($check_in, FILTER_SANITIZE_STRING);
   $check_out = $_POST['check_out'];
   $check_out = filter_var($check_out, FILTER_SANITIZE_STRING);
   $adults = $_POST['adults'];
   $adults = filter_var($adults, FILTER_SANITIZE_STRING);
   $childs = $_POST['childs'];
   $childs = filter_var($childs, FILTER_SANITIZE_STRING);

   $total_rooms = 0;

   $check_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE check_in = ?");
   $check_bookings->execute([$check_in]);

   while($fetch_bookings = $check_bookings->fetch(PDO::FETCH_ASSOC)){
      $total_rooms += $fetch_bookings['rooms'];
   }

   if($total_rooms >= 30){
      $warning_msg[] = 'rooms are not available';
   }else{

      $verify_bookings = $conn->prepare("SELECT * FROM `bookings` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND rooms = ? AND check_in = ? AND check_out = ? AND adults = ? AND childs = ?");
      $verify_bookings->execute([$user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);

      if($verify_bookings->rowCount() > 0){
         $warning_msg[] = 'room booked alredy!';
      }else{
         $book_room = $conn->prepare("INSERT INTO `bookings`(booking_id, user_id, name, email, number, rooms, check_in, check_out, adults, childs) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $book_room->execute([$booking_id, $user_id, $name, $email, $number, $rooms, $check_in, $check_out, $adults, $childs]);
         $success_msg[] = 'room booked successfully!';
      }

   }

}

if(isset($_POST['send'])){

   $id = create_unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $message = $_POST['message'];
   $message = filter_var($message, FILTER_SANITIZE_STRING);

   $verify_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $verify_message->execute([$name, $email, $number, $message]);

   if($verify_message->rowCount() > 0){
      $warning_msg[] = 'message sent already!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `messages`(id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$id, $name, $email, $number, $message]);
      $success_msg[] = 'message send successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Hotel</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/styles.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- home section starts  -->

<section class="home" id="home">

   <div class="swiper home-slider">

      <div class="swiper-wrapper">

         <div class="box swiper-slide">
            <img src="imgs/home-img-1.jpg" alt="">
            <div class="flex">
               <h3>Розкішні кімнати</h3>
               <a href="#availability" class="btn">Перевірити наявність!</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="imgs/home-img-2.jpg" alt="">
            <div class="flex">
               <h3>Їжа та напої</h3>
               <a href="#reservation" class="btn">Забронювати!</a>
            </div>
         </div>

         <div class="box swiper-slide">
            <img src="imgs/home-img-3.jpg" alt="">
            <div class="flex">
               <h3>Дивовижні холи</h3>
               <a href="#contact" class="btn">Напишіть нам!</a>
            </div>
         </div>

      </div>

      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>

   </div>

</section>

<!-- home section ends -->

<!-- availability section starts  -->

<section class="availability" id="availability">

   <form action="" method="post">
      <div class="flex">
         <div class="box">
            <p>Заїзд <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Виїзд <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>Дорослі <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
            </select>
         </div>
         <div class="box">
            <p>Діти <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="-">0</option>
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
            </select>
         </div>
         <div class="box">
            <p>Кімнати <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
            </select>
         </div>
      </div>
      <input type="submit" value="Перевірити наявність" name="check" class="btn">
   </form>

</section>

<!-- availability section ends -->

<!-- about section starts  -->

<section class="about" id="about">

   <div class="row">
      <div class="image">
         <img src="imgs/about-img-1.jpg" alt="">
      </div>
      <div class="content">
         <h3>Найкращий колектив</h3>
         <p>Протягом всього вашого перебування в готелі наш колектив зробить все можливе для організації вашого комфорту, та подбає про те, щоб цей відпочинок запам'ятався вам надовго!</p>
         <a href="#reservation" class="btn">Забронювати</a>
      </div>
   </div>

   <div class="row revers">
      <div class="image">
         <img src="imgs/about-img-2.jpg" alt="">
      </div>
      <div class="content">
         <h3>Вишукана їжа</h3>
         <p>В ресторані нашого готелю ви можете спробувати різноманітні страви багатьох кухонь світу, звісно ж, від найкращих шефів країни</p>
         <a href="#contact" class="btn">Напишіть нам</a>
      </div>
   </div>

   <div class="row">
      <div class="image">
         <img src="imgs/about-img-3.jpg" alt="">
      </div>
      <div class="content">
         <h3>Басейн</h3>
         <p>Також ваш відпочинок будуть доповнювати прекрасні басейни, які розсташовані практично на всій території готелю</p>
         <a href="#availability" class="btn">Перевірити наявність</a>
      </div>
   </div>

</section>

<!-- about section ends -->

<!-- services section starts  -->

<section class="services">

   <div class="box-container">

      <div class="box">
         <img src="imgs/icon-1.png" alt="">
         <h3>Їжа та напої</h3>
         <p>Насолоджуйтесь розкішними напоями та їжею від найкращих шефів країни</p>
      </div>

      <div class="box">
         <img src="imgs/icon-2.png" alt="">
         <h3>Вечеря на узбережжі</h3>
         <p>Влаштуйте неперевершену вечерю на узбережжі моря</p>
      </div>

      <div class="box">
         <img src="imgs/icon-3.png" alt="">
         <h3>Мальовничі пейзажі</h3>
         <p>Обов'язково відвідайте природний заповідник на території готелю</p>
      </div>

      <div class="box">
         <img src="imgs/icon-4.png" alt="">
         <h3>Декорації</h3>
         <p>Ми намагаємося прикрасити наш готель, щоб відвідувачі отримали справжнє задоволення</p>
      </div>

      <div class="box">
         <img src="imgs/icon-5.png" alt="">
         <h3>Басейн</h3>
         <p>Чекаємо вас на різноманітних активностях кожного дня, які відбуваються на території басейнів</p>
      </div>

      <div class="box">
         <img src="imgs/icon-6.png" alt="">
         <h3>Пляж готелю</h3>
         <p>Пляж - візитна картка нашого готелю. Вам обов'язково сподобається</p>
      </div>

   </div>

</section>

<!-- services section ends -->

<!-- reservation section starts  -->

<section class="reservation" id="reservation">

   <form action="" method="post">
      <h3>Забронювати</h3>
      <div class="flex">
         <div class="box">
            <p>Ім'я <span>*</span></p>
            <input type="text" name="name" maxlength="50" required placeholder="введіть ваше ім'я" class="input">
         </div>
         <div class="box">
            <p>Емейл <span>*</span></p>
            <input type="email" name="email" maxlength="50" required placeholder="введіть ваш емейл" class="input">
         </div>
         <div class="box">
            <p>Номер <span>*</span></p>
            <input type="number" name="number" maxlength="10" min="0" max="9999999999" required placeholder="введіть ваш номер телефону" class="input">
         </div>
         <div class="box">
            <p>Кімнати <span>*</span></p>
            <select name="rooms" class="input" required>
               <option value="1" selected>1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
            </select>
         </div>
         <div class="box">
            <p>Заїзд <span>*</span></p>
            <input type="date" name="check_in" class="input" required>
         </div>
         <div class="box">
            <p>Виїзд <span>*</span></p>
            <input type="date" name="check_out" class="input" required>
         </div>
         <div class="box">
            <p>Дорослі <span>*</span></p>
            <select name="adults" class="input" required>
               <option value="1" selected>1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
            </select>
         </div>
         <div class="box">
            <p>Діти <span>*</span></p>
            <select name="childs" class="input" required>
               <option value="0" selected>0</option>
               <option value="1">1</option>
               <option value="2">2</option>
               <option value="3">3</option>
               <option value="4">4</option>
               <option value="5">5</option>
            </select>
         </div>
      </div>
      <input type="submit" value="Забронювати" name="book" class="btn">
   </form>

</section>

<!-- reservation section ends -->

<!-- gallery section starts  -->

<section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="imgs/gallery-img-1.jpg" class="swiper-slide" alt="">
         <img src="imgs/gallery-img-2.webp" class="swiper-slide" alt="">
         <img src="imgs/gallery-img-3.webp" class="swiper-slide" alt="">
         <img src="imgs/gallery-img-4.webp" class="swiper-slide" alt="">
         <img src="imgs/gallery-img-5.webp" class="swiper-slide" alt="">
         <img src="imgs/gallery-img-6.webp" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- gallery section ends -->

<!-- contact section starts  -->

<section class="contact" id="contact">

   <div class="row">

      <form action="" method="post">
         <h3>Надішліть нам повідомлення!</h3>
         <input type="text" name="name" required maxlength="50" placeholder="Ім'я" class="box">
         <input type="email" name="email" required maxlength="50" placeholder="Емейл" class="box">
         <input type="number" name="number" required maxlength="10" min="0" max="999999999999" placeholder="Номер" class="box">
         <textarea name="message" class="box" required maxlength="1000" placeholder="Введіть ваше повідомлення" cols="30" rows="10"></textarea>
         <input type="submit" value="Надіслати" name="send" class="btn">
      </form>

      <div class="faq">
         <h3 class="title">Актуальні питання</h3>
         <div class="box active">
            <h3>Як забронювати номер?</h3>
            <p>Ви можете забронювати потрібну вам кімнату на вебсайті нашого готелю!</p>
         </div>
         <div class="box">
            <h3>Є вакансії?</h3>
            <p>Наразі колектив сформований, якщо буде потреба в доукомплектації - ми вас обов'язково сповістимо!</p>
         </div>
         <div class="box">
            <h3>Які доступні методи оплати?</h3>
            <p>Ви можете оплатити послуги готелю кредитною карткою на сайті готелю, або готівкою на рецепції</p>
         </div>
         <div class="box">
            <h3>Як отримати бонусні купони?</h3>
            <p>Ви отримуєте бонусні купони за кожного запрошеного вами друга, який скористається послугами готелю</p>
         </div>
         <div class="box">
            <h3>Є якісь вікові обмеження?</h3>
            <p>Ми пропонуємо відпочинок для людей різних вікових категорій.В нас буде весело як дітям, так і дорослим </p>
         </div>
      </div>

   </div>

</section>

<!-- contact section ends -->

<!-- reviews section starts  -->

<section class="reviews" id="reviews">

   <div class="swiper reviews-slider">

      <div class="swiper-wrapper">
         <div class="swiper-slide box">
            <img src="imgs/pic-1.png" alt="">
            <h3>Остап Жук</h3>
            <p>Просторий двокімнатний номер, чиста і якісна постільна білизна, привітний персонал,швидкий інтернет,до центра міста можно прогулятися пішки, або міським транспортом ,за додаткову плату парковка на 4 авто.</p>
         </div>
         <div class="swiper-slide box">
            <img src="imgs/pic-2.png" alt="">
            <h3>Яна Гук</h3>
            <p>Відмінне розміщення та зручність доступу, магазин в будинку цілодобово, є стоянка, І, без проблем. Велика площа.</p>
         </div>
         <div class="swiper-slide box">
            <img src="imgs/pic-3.png" alt="">
            <h3>Марко</h3>
            <p>природа, небагато людей, тиша, можливість смажити на мангалі, спортзал та басейн, наявність паркінгу</p>
         </div>
         <div class="swiper-slide box">
            <img src="imgs/pic-4.png" alt="">
            <h3>Оксана</h3>
            <p>Сподобалося все. швидке поселення, ввічливий персонал, чисті комфортні номери, наявність басейну, віддаленість від центру, але пішки дійти не проблема. Ще смачні сніданки- на будь який смак. У Львові завжди багато позитивних вражень! Мені щастить з цим містом.</p>
         </div>
         <div class="swiper-slide box">
            <img src="imgs/pic-5.png" alt="">
            <h3>Олег</h3>
            <p>Зручне розташування в центрі, близько до всіх локацій. Окремий вхід.</p>
         </div>
         <div class="swiper-slide box">
            <img src="imgs/pic-6.png" alt="">
            <h3>Анастасія</h3>
            <p>Не сподобалися сусіди, стіни дуже тонкі - все чутно.</p>
         </div>
      </div>

      <div class="swiper-pagination"></div>
   </div>

</section>

<!-- reviews section ends  -->





<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/message.php'; ?>

</body>
</html>