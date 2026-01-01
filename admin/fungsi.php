<?php 
require '../db_connect.php';

function query($query) {
        global $conn;
        $result = mysqli_query($conn, $query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)){
            $rows[] = $row;
        }
        return $rows;

    }   

function cariUser($key){
    global $conn;
    $query = "SELECT 
    u.user_id, 
    u.name, 
    u.email, 
    u.nomor, 
    COUNT(o.order_id) AS total_orders
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    WHERE (role = 'customer' OR role = '')
    AND (name LIKE '%$key%' OR email LIKE '%$key%' OR nomor LIKE '%$key%')
    GROUP BY 
    u.user_id, 
    u.name, 
    u.email, 
    u.nomor;
    ";
    return query($query);
}

function editUser($data){ 
    global $conn;
    $id = $data['user_id'];
    $nama = $data['name'];
    $email = $data['email'];
    $hp =$data['nomor'];  
    
        $query = "UPDATE users SET 
            name = '$nama',email = '$email',nomor = '$hp' where user_id = $id;"; 

        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);
}

    function hapusUser($idhapus){
        global $conn;

        $query = "DELETE FROM users where user_id = $idhapus";

        mysqli_query($conn, $query);

        return mysqli_affected_rows($conn);
    }

    function upload(){
        $namaFile = $_FILES['gambar']['name'];
        $tipeFile = $_FILES['gambar']['type'];
        $tmpFile = $_FILES['gambar']['tmp_name'];
        $error = $_FILES['gambar']['error'];
        $size = $_FILES['gambar']['size'];
        

        //cek tipe file
        $ekstensiyangboleh = ["jpeg", "png", "jpg"];
        $ekstensigambar = explode(".",$namaFile);
        $ekstensigambar = strtolower(end($ekstensigambar));
        $namaFile = $_FILES['gambar']['name'];

        //cek error
        if ($error === 4){
            echo "
                <script>
            alert('Anda belum memilih gambar.');
            
        </script>
             ";
             return false;
        }

        if (!in_array($ekstensigambar,$ekstensiyangboleh)){
            echo "
                <script>
            alert('hanya menerima jpeg, jpg, png');
            
        </script>
             ";
             return false;        
        }

        //cek size
        if ($size > 2097152){
            echo "
                <script>
            alert('2 megabyte limit.');
            
        </script>";

             return false;
        }

        move_uploaded_file($tmpFile, "../img/".$namaFile);

        return $namaFile;     
    }

    function tambahMenu($data) {
        global $conn;
        $cat = $data['category'];
        $nama = $data['name'];
        $des = $data['description'];
        $price = $data['price'];        
        $gambar = upload();
        if (!$gambar){
            return false;
        }

        $query = "INSERT INTO menu VALUES ('','$cat','$nama','$des','$price','$gambar');"; 

        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);
    }

    function editMenu($data){ 
        global $conn;
        $id = $data['menu_id'];
        $cat = $data['category'];
        $nama = $data['name'];
        $des = $data['description'];
        $price = $data['price'];
        $gambar = $data['gambar'];
        $gambarLama = $data['gambarLama'];

        if ($_FILES['gambar']['error'] === 4){
            $gambar = $gambarLama;
        }else{
            $gambar = upload();
        }     

        $query = "UPDATE menu SET 
            category = '$cat', name = '$nama', description = '$des',price = '$price', image = '$gambar' where menu_id = $id;"; 

        mysqli_query($conn,$query);

        return mysqli_affected_rows($conn);

    }

    function hapusMenu($idhapus){
        global $conn;

        $query = "DELETE FROM menu where menu_id = $idhapus";

        mysqli_query($conn, $query);

        return mysqli_affected_rows($conn);
    }

    function hapusOrder($idhapus){
        global $conn;

        $query1 = "DELETE FROM order_items where order_id = $idhapus";
        $query2 = "DELETE FROM orders where order_id = $idhapus";


        mysqli_query($conn, $query1);
        mysqli_query($conn, $query2);

        return mysqli_affected_rows($conn);
    }

?>