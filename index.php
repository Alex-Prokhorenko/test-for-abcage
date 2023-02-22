<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>test for abcAge</title>
    <link href="styles/style.css" rel="stylesheet">
</head>
<body>


<form action="" method="POST" class="form">
    <label for="product_id">Товар: </label><select name="product_id" id="product_id">
        <option value="1">Колбаса</option>
        <option value="2">Пармезан</option>
        <option value="3" selected>Левый носок</option>
    </select>
    <label for="date">Дата: </label><input type="date" name="date" id="date" placeholder="введите дату">
    <label for="consignment">Количество товара: </label><input type="number" name="consignment" id="consignment" min="1"
                                                               placeholder="количество отправляемого товара">
    <input type="submit" value="Получить цену">
</form>
<div class="message">
    <?php
    function connectToDB()
    {
        $host = 'localhost';
        $dbName = 'storage';
        $userName = 'root';
        $pass = '';
        try {
            $dbh = new PDO("mysql:host=$host;dbname=$dbName", $userName, $pass);
        } catch (PDOException $e) {
            echo "<p><b>Ошибка!: " . $e->getMessage() . "</b></p>";
            die();
        }
        return mysqli_connect($host, $userName, $pass, $dbName);
    }

    function getProductsFrom($table)
    {
        $link = connectToDB();
        $productId = $_POST['product_id'];
        $date = $_POST['date'];

        //Получаем количество товаров из таблицы на определенную дату
        $query = "SELECT * FROM $table WHERE product_id = '$productId' AND date <= '$date'";
        $result = mysqli_query($link, $query) or die(mysqli_error($link));
        for ($suppliesProduct = []; $row = mysqli_fetch_assoc($result); $suppliesProduct[] = $row) ;

        //Делим стоимость товара на его количество в каждой поставке, чтобы узнать среднюю цену за единицу на текущую дату
        $averageCost = 0;
        $productAmount = 0;
        $supplyAmount = 0;
        foreach ($suppliesProduct as $row) {
            $supplyAmount++;
            $averageCost += $row['cost'] / $row['amount'];
            $productAmount += $row['amount'];
        }
        $averageCost = $supplyAmount > 0 ? round($averageCost / $supplyAmount, 2)
        : round($averageCost, 2);
        return [$averageCost, $productAmount];
    }

    function getActualData()
    {
        $consignment = $_POST['consignment'];
        $arrivedProduct = getProductsFrom('supplies');
        $sentProduct = getProductsFrom('pre_orders');
        $actualAmount = $arrivedProduct[1] - $sentProduct[1];
        $cost = $arrivedProduct[0];
        ?>
        <div>
            <table>
                <tr>
                    <th>Остаток на складе</th>
                    <th>Текущая цена за единицу</th>
                    <th>Отправляемая партия</th>
                    <th>Цена партии с наценкой</th>
                </tr>
                <tr>
                    <td><?= $actualAmount ?> шт.</td>
                    <td><?= $cost ?> р.</td>
                    <td><?= $consignment ?> шт.</td>
                    <td><?= round($consignment * ($cost * 1.3),2) ?> р.</td>
                </tr>
            </table>
        </div>
        <?php
    }


    function validateFormatDate($date)
    {
        return !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }

    function validateDate($date)
    {
        [$year, $month, $day] = explode('-', $date);
        return (!checkdate($month, $day, $year));
    }

    function sendQuery($date)
    {
        if (validateFormatDate($date)) {
            echo "<p><b>Некорректный формат даты</b></p>";
        } else if (validateDate($date)) {
            echo "<p><b>Введена некорректная дата</b></p>";
        } else getActualData();
    }

    if (isset($_POST['product_id']) && isset($_POST['date']) && !empty($_POST['consignment'])) {
        sendQuery($_POST['date']);
    } else echo "<p><b>Для отправки запроса заполните все поля</b></p>"
    ?>
</div>
</body>
</html>

