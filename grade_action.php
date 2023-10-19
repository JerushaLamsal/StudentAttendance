<?php

include "database_connection.php";

session_start();

$output = "";

if (isset($_POST["action"])) {
    if ($_POST["action"] == "fetch") {
        $query = "SELECT * FROM tbl_grade ";
        if (isset($_POST["search"]["value"])) {
            $query .=
                'WHERE grade_name LIKE "%' . $_POST["search"]["value"] . '%" ';
        }
        if (isset($_POST["order"])) {
            $query .=
                "ORDER BY " .
                $_POST["order"]["0"]["column"] .
                "  " .
                $_POST["order"]["0"]["dir"] .
                " ";
        } else {
            $query .= "ORDER BY grade_id DESC";
        }
        if (isset($_POST["length"])) {
            if ($_POST["length"] != -1) {
                $query .= "LIMIT" . $_POST["start"] . ", " . $_POST["length"];
            }
        }
        $statement = $connect->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        $data = [];
        $filtered_rows = $statement->rowCount();
        foreach ($result as $row) {
            $sub_array = [];
            $sub_array[] = $row["grade_name"];
            $sub_array[] =
                '<button type="button" name="edit_grade" class="btn btn-primary btn-sm edit_grade" id=" ' .
                $row["grade_id"] .
                ' ">Edit</button>';

            $sub_array[] =
                '<button type="button" name="delete_grade" class="btn btn-danger btn-sm delete_grade" id=" ' .
                $row["grade_id"] .
                ' ">Delete</button>';
            $data[] = $sub_array;
        }

        $output = [
            "draw" => intval(["1"]),
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => get_total_records($connect, " tbl_grade"),
            "data" => $data,
        ];

        echo json_encode($output);
    }
    else if ($_POST["action"] == "Add" || $_POST["action"] == "Edit") {
        $grade_name = "";
        $error_grade_name = "";
        $error = 0;
        if (empty($_POST["grade_name"])) {
            $error_grade_name = "Grade Name is required";
            $error++;
            if ($error > 0) {
                $output = [
                    "error" => true,
                    "error_grade_name" => $error_grade_name,
                ];
            }
        } else {
            $grade_name = $_POST["grade_name"];
            if ($_POST["action"] == "Add") {
              
                $data = [
                    ":grade_name" => $grade_name,
                ];
                $query =
                    "INSERT INTO tbl_grade (grade_name) values (:grade_name)";

                $statement = $connect->prepare($query);
                if ($statement->execute($data)) {
                    if ($statement->rowCount() > 0) {
                        $output = ["success" => "Data Added Successfully"];
                    } else {
                        $output = [
                            "error" => true,
                            "error_grade_name" => "Grade Name Already Exists",
                        ];
                    }
                }
            }
            if ($_POST["action"] == "Edit") {
                $data = [
                    ":grade_name" => $grade_name,
                    ":grade_id" => $_POST["grade_id"],
                ];

                $query = "UPDATE tbl_grade SET grade_name =:grade_name WHERE grade_id =:grade_id";
                $statement = $connect->prepare($query);
                if ($statement->execute($data)) {
                    $output = ["success" => "Data Updated Successfully"];
                }
            }
        }
        echo json_encode($output);
    }

    else if ($_POST["action"] == "edit_fetch") {
        $query = "SELECT * FROM tbl_grade WHERE grade_id = '" .$_POST["grade_id"] ."'";
        $statement = $connect->prepare($query);
        if ($statement->execute()) {
            $result = $statement->fetchAll();
            foreach ($result as $row) {
               $output = [

                  "grade_name" => $row["grade_name"],

                  "grade_id" => $row["grade_id"],

              ];
            }
        }
        echo json_encode($output);
    }

    if ($_POST["action"] == "delete") {
        $query =
            " DELETE FROM tbl_grade WHERE grade_id = '" .
            $_POST["grade_id"] .
            "'";
        $statement = $connect->prepare($query);
        if ($statement->execute()) {
            echo "Data Delete Successfully";
        }
    }
}
?>