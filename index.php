<?php 
// (42)((412)14(4))-2)-2(2
  function check_parentheses($str) {
    $stack = array();
    $array = str_split($str);
    foreach ($array as $char) {
      if (preg_match("/\(/m", $char)) {
        array_push($stack, $char);
      }
      if (preg_match("/\)/m", $char)) {
        array_pop($stack);
      }
    }
    if (count($stack) !== 0) {
      throw new Exception("Неверная последовательность '(' и ')'", 1);
    }
  }
  function validate($str) {
    if (empty($str)){
      throw new Exception("Строка пуста", 1);
    }
    if (preg_match("/[^0-9+\-*:,\.\/() ]/m", $str)){
      throw new Exception("Недопустимые символы", 1);
    }
    if(preg_match("/[^\d()]{2,}/m", $str)){
      throw new Exception("Неверная запись", 1);
    }
    if (preg_match("/\(/m", $str)){
      if(preg_match_all("/\(/m", $str) !== preg_match_all("/\)/m", $str)){
        throw new Exception("Колличество '(' и ')' не совпадает", 1);
      }
      if(!preg_match("/.*\(.*\)(.*\(.*\).*)*.*/m", $str) ){
        throw new Exception("Неверная последовательность '(' и ')'", 1);
      }
      if(preg_match("/\(\)/m", $str) ){
        throw new Exception("Скобки не должны быть пустыми ", 1);
      }
      check_parentheses($str);
    } elseif (preg_match("/\)/m", $str)){
      echo "$str";
      throw new Exception("Выражение не может содержать только ')' ", 1);
    }
    // echo "ok $str <br>";
    return $str;
  }
  function rpn($str){
  $stack=array(); //объявляем массив стека
  $out=array(); //объявляем массив выходной строки
  $prior = array ( //задаем приоритет операторов, а также их ассоциативность
      "^"=> array("prior" => "4", "assoc" => "right"),
      "*"=> array("prior" => "3", "assoc" => "left"),
      "/"=> array("prior" => "3", "assoc" => "left"),
      "+"=> array("prior" => "2", "assoc" => "left"),
      "-"=> array("prior" => "2", "assoc" => "left"),
  );
  $token=preg_replace("/\s/", "", $str); //удалим все пробелы
  $token=str_replace(",", ".", $token);//поменяем запятые на точки
  $token = str_split($token);
  /*проверим, не является ли первый символ знаком операции - тогда допишем 0 перед ним */
  if (preg_match("/[\+\-\*\/\^]/",$token['0'])){array_unshift($token, "0");}
  $lastnum = TRUE; //в выражении теперь точно первым будет идти число - поставим маркер
  foreach ($token as $key=>$value)
  {
    // echo("<HR><br>elem=".$value." matched=".preg_match("/[\+\-\*\/\^]/",$value));
    if (preg_match("/[\+\-\*\/\^]/",$value))//если встретили оператор
      {
        $endop = FALSE; //маркер конца цикла разбора операторов
        while ($endop != TRUE)
        { 
          // echo("<br>stack: ");
          // print_r($stack);

          $lastop = array_pop($stack);

          // echo("<br> lastop = ");
          // print_r($lastop);

          if ($lastop=="")
          {
            $stack[]=$value; //если в стеке нет операторов - просто записываем текущий оператор в стек
            $endop = TRUE; //укажем, что цикл разбора while закончился
          }
          else //если в стеке есть операторы - то последний сейчас в переменной $lastop
          {
            /* получим приоритет и ассоциативность текущего оператора и сравним его с $lastop */
            $curr_prior = $prior[$value]['prior']; //приоритет текущиего оператора
            $curr_assoc = $prior[$value]['assoc']; //ассоциативность текущиего оператора
            if (isset($prior[$lastop])){
              $prev_prior = $prior[$lastop]['prior'];
            }else {
              $prev_prior = null
            } //приоритет предыдущего оператора
            switch ($curr_assoc) //проверяем текущую ассоциативность
            {
              case "left": //оператор - лево-ассоциативный
                switch ($curr_prior) //проверяем текущий приоритет лево-ассоциаивного оператора
                {
                  case ($curr_prior > $prev_prior): //если приоритет текущего опертора больше предыдущего, то записываем в стек предыдущий, потом текйщий
                    $stack[]=$lastop;
                    $stack[]=$value;
                    $endop = TRUE; //укажем, что цикл разбора операторов while закончился
                    break;
                  case ($curr_prior <= $prev_prior): //если тек. приоритет меньше или равен пред. - выталкиваем пред. в строку out[]
                    $out[] = $lastop;
                    break;
                }
              break;
              case "right": //оператор - право-ассоциативный
                switch ($curr_prior) //проверяем текущий приоритет право-ассоциативного оператора
                {
                  case ($curr_prior >= $prev_prior): //если приоритет текущего опертора больше или равен предыдущего, то записываем в стек предыдущий, потом текйщий
                    $stack[]=$lastop;
                    $stack[]=$value;
                    $endop = TRUE; //укажем, что цикл разбора операторов while закончился
                    break;
                  case ($curr_prior < $prev_prior): //если тек. приоритет меньше пред. - выталкиваем пред. в строку out[]
                    $out[] = $lastop;
                    break;
                }   
              break;
            }
          }
          // echo("<br>stack2: ");
          // print_r($stack);
        } //while ($endop != TRUE)
        $lastnum = false; //укажем, что последний разобранный символ - не цифра
      }
    elseif (preg_match("/[0-9\.]/",$value)) //встретили цифру или точку
      {
    /*Мы встретили цифру или точку (дробное число). Надо понять, какой символ был разобран перед ней. 
    За это отвечает переменная $lastnum - если она TRUE, то последней была цифра.
    В этом случае надо дописать текущую цифру к последнему элменту массива выходной строки*/
        if ($lastnum == TRUE) //последний разобранный символ - цифра
          {
            $num = array_pop($out); //извлечем содержимое последнего элемента массива строки
            $out[]=$num.$value;
          }
        else 
          {
            $out[] = $value; //если последним был знак операции - то открываем новый элемент массива строки
            $lastnum = TRUE; //и указываем, что последним была цифра
          }
      }
    elseif ($value=="(") //встреили скобку ОТкрывающую
      {
              // echo "<br>скобка (<br>";
    /*Мы встретили ОТкрывающую скобку - надо просто поместить ее в стек*/
            $stack[] = $value; 
            $lastnum = FALSE; // указываем, что последним была НЕ цифра
      }
    elseif ($value==")") //встреили скобку ЗАкрывающую
      {
      // echo "<br>скобка )<br>";
    /*Мы встретили ЗАкрывающую скобку - теперь выталкиваем с вершины стека в строку все операторы, пока не встретим ОТкрывающую скобку*/
            $skobka = FALSE; //маркер нахождения открывающей скобки
            while ($skobka != TRUE) //пока не найдем в стеке ОТкрывающую скобку
            {
              $op = array_pop($stack); //берем оператора с вершины стека
                if ($op == "(") 
                {
                  $skobka = TRUE; //если встретили открывающую - меняем маркер
                } 
                else
                {
                  $out[] = $op; //если это не скобка - отправляем символ в строку
                }
            }
            $lastnum = FALSE; //указываем, что последним была НЕ цифра
      } 
  }
  /*foreach закончился - мы разобрали все выражение
  теперь вытолкнем все оставшиеся элементы стека в выходную строку, начиная с вершины стека*/
  $stack1 = $stack; //временный массив, копия стека, на случай, если будет нужен сам стек для дебага
  $rpn = $out; //начинаем формировать итоговую строку
  while ($stack_el = array_pop($stack1))
  {
    $rpn[]=$stack_el;
  }
  $rpn_str = implode(" ", $rpn); //запишем итоговый массив в строку
  return $rpn_str; //функция возвращает строку, в которой исходное выражение представлено в ОПЗ
}



function calc($str)
{
  $stack = array();
  
  $token = strtok($str, ' ');
  
  while ($token !== false)
  {
    if (in_array($token, array('*', '/', '+', '-', '^')))
    {
      if (count($stack) < 2){
        throw new Exception("Недостаточно данных в стеке для операции '$token'");
      }
      $b = array_pop($stack);
      $a = array_pop($stack);
      switch ($token)
      {
        case '*': $res = $a*$b; break;
        case '/': if ($b != 0) {
          $res = $a/$b;
          } else throw new Exception("Деление на 0", 1);
        break;
        case '+': $res = $a+$b; break;
        case '-': $res = $a-$b; break;
        case '^': $res = pow($a,$b); break;

      }
      array_push($stack, $res);
    } elseif (is_numeric($token))
    {
      array_push($stack, $token);
    } else
    {
      throw new Exception("Недопустимый символ в выражении: $token");
    }

    $token = strtok(' ');
  }
  if (count($stack) > 1)
    throw new Exception("Количество операторов не соответствует количеству операндов");
  return array_pop($stack);
}

?>
<!DOCTYPE html>
<html>
<head>
  <title>Луткова</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<div class="container">
  <div class="row">
    <h2 class="mt-3">Лабораторная работа 2</h2>
  </div>
  <div class="row">
    <form method="POST">
      <div class="form-group">
        <label for="valueInput">Калькулятор</label>
        <input 
        type="text" 
        class="form-control" 
        id="valueInput" 
        aria-describedby="valueHelp" 
        placeholder="2 + 2" 
        name="value" 
        required>
        <small id="valueHelp" class="form-text text-muted">Введите вычисляемое выражение и нажмите кнопу "Вычислить"</small>
      </div>
      <button type="submit" class="btn btn-primary" name="send">Вычислить</button>
    </form>
  </div>
  <div class="row mt-3 flex-column">
      <?php
        session_start();
          if(!empty($_SESSION)){
            $store = $_SESSION;
          }
          // print_r($_SESSION);
        
        if (isset($_POST['send'])&&!empty($_POST)){
          $value = str_replace(' ', '', $_POST['value']);
          try {
            $value = validate($value);
            $rpn_str = rpn($value);
            echo "$rpn_str";
            $result = calc($rpn_str);
            // echo("<br><b>rpn: $value</b>");
            echo "<div class='alert alert-success' role='alert'> Ответ: ".$result." </div>";
          } catch (Exception $e) {
            $result = $e -> getMessage();
           echo "<div class='alert alert-danger' role='alert'> [Ошибка]: ".$result." </div>";
           // echo("<br> $value");
          }
            $id = count($_SESSION);
            $item = $arrayName = array('value' => $value, 'result' => $result);
            $_SESSION["id$id"] = $item;
        }
        if (isset($store)){
          echo "<h3>История вычислений</h3>";
          echo "<div class=' flex-column'>";
          foreach ($store as $key => $value) {
              // print_r($value['value']);
              // echo("<br>");
              echo "<div class='alert alert-primary' role='alert'>".$value['value']." = ".$value['result']."</div>";
          }
          echo "</div>";
        }
       ?>
    </div>
</div>
</body>
<style type="text/css">
  input[type='submit'] {
    color: #fff;
  }
</style>
</html>