<?php

class Attdb extends CI_Controller
{
    public $attdb;
    protected $tablename = 'CHECKINOUT';
    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '-1');
        $this->attdb = $this->load->database('tiga', TRUE);
        $this->load->helper('form');
    }

    function index()
    {
        echo "List Absensi- " . date('d-M-Y') . "<br>";
        $attdb = $this->load->database('tiga', TRUE);
        $Y = DATE('Y');
        $M = date('m');
        $D = date('d');
        $data = $attdb->query("SELECT * FROM CHECKINOUTVIEW WHERE YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D ORDER BY CHECKTIME DESC")->result();
        $table = "<table border=1>"
            . "<thead>"
            . "<tr>"
            . "<th>No.</th>"
            . "<th>User Id</th>"
            . "<th>User Name</th>"
            . "<th>Check Time</th>"
            . "<th>Check Type</th>"
            . "</tr>"
            . "</thead>";

        $tr = null;
        foreach ($data as $k => $v) {
            $no = (int) $k + 1;
            $tr .= "<tr>"
                . "<td>$no</td>"
                . "<td>$v->USERID</td>"
                . "<td>$v->Name</td>"
                . "<td>$v->CHECKTIME</td>"
                . "<td>$v->CHECKTYPE</td>"
                . "</tr>";
        }
        $endtable = $table . $tr . "</table>";
        echo $endtable;
    }

    public function transferbydate()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "POST") {
            $Y = $this->input->post('tahun', TRUE);
            $M = $this->input->post('bulan', TRUE);
            $D = $this->input->post('tanggal');
        } else if ($method == "GET") {
            $Y = $this->input->get('tahun', TRUE);
            $M = $this->input->get('bulan', TRUE);
            $D = $this->input->get('tanggal');
        }
        echo json_encode([
            "tahun" => $Y,
            "bulan" => $M,
            "tanggal" => $D,
            "method" => $_SERVER['REQUEST_METHOD']
        ]);
        echo "<hr>";
        $this->transferin($Y, $M, $D);
        $this->transferout($Y, $M, $D);
    }

    public function transfer()
    {
        $Y = DATE('Y');
        $M = date('m');
        $D = date('d');
        $this->transferin($Y, $M, $D);
        $this->transferout($Y, $M, $D);
    }

    public function transferin($Y, $M, $D)
    {
        $data = $this->attdb->query("SELECT DISTINCT USERID  FROM CHECKINOUT WHERE CHECKTYPE='I' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D");
        $date = $M . '/' . $D . '/' . $Y;
        $data = $this->attdb->query("SELECT DISTINCT USERID FROM CHECKINOUT WHERE CHECKTIME LIKE '%$date%' AND CHECKTYPE='I' AND VERIFYCODE=0");

        $i = 0;
        $l = count($data->result());
        print_r($l);
        return;
        echo $l . "<hr>";
        if ((int) $l > 0) {
            while ($l > $i) {
                $datasatu = $this->attdb->query("SELECT TOP 1 *  FROM CHECKINOUTVIEW WHERE CHECKTYPE='I' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D ORDER BY CHECKTIME ASC")->result();
                if (count($datasatu) <= 0) {
                    echo "no data ";
                    return;
                }
                if ((int) $i >= (int) $l) {
                    echo "break <hr><hr>";
                    break;
                }
                $i += 1;
                echo json_encode($data);
                echo "<hr>";
                foreach ($datasatu as $k => $v) {
                    $_USERID = $v->USERID;
                    $_CHECKTIME = $v->CHECKTIME;
                    $_TAHUN = $v->TAHUN;
                    $_BULAN = $v->BULAN;
                    $_TANGGAL = $v->TANGGAL;
                    $idata = [
                        'USERID' => $v->USERID,
                        'CHECKTIME' => $v->CHECKTIME,
                        'CHECKTYPE' => $v->CHECKTYPE,
                        'VERIFYCODE' => $v->VERIFYCODE,
                        'status' => 0,
                        'transferdate' => date('Y-m-d H:i:s'),
                        'istransfer' => $l
                    ];
                }
                echo json_encode($idata);
                echo "<hr>";

                $this->db->insert('t_attendance_temp', $idata);
                if ($this->db->affected_rows() > 0) {
                    $this->attdb->query("UPDATE CHECKINOUT SET VERIFYCODE=1 WHERE USERID=$_USERID AND CHECKTYPE='I' AND YEAR(CHECKTIME)=$_TAHUN AND MONTH(CHECKTIME)=$_BULAN AND DAY(CHECKTIME)=$_TANGGAL");
                }
                echo 'berhasil IN berhasil';
            }
        } else {
            echo '<br>=> tidak ada data IN<br>';
        }
    }

    public function transferout($Y, $M, $D)
    {
        $data = $this->attdb->query("SELECT DISTINCT USERID  FROM CHECKINOUT WHERE CHECKTYPE='O' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D");
        $i = 0;
        $l = count($data->result());
        echo $l . "<hr>";
        if ((int) $l > 0) {
            while ($l > $i) {
                $datasatu = $this->attdb->query("SELECT TOP 1 *  FROM CHECKINOUTVIEW WHERE CHECKTYPE='O' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D ORDER BY CHECKTIME DESC")->result();
                if (count($datasatu) <= 0) {
                    echo "no data ";
                    return;
                }
                if ((int) $i >= (int) $l) {
                    echo "break <hr><hr>";
                    break;
                }
                $i += 1;
                echo json_encode($data);
                echo "<hr>";
                foreach ($datasatu as $k => $v) {
                    $_USERID = $v->USERID;
                    $_CHECKTIME = $v->CHECKTIME;
                    $_TAHUN = $v->TAHUN;
                    $_BULAN = $v->BULAN;
                    $_TANGGAL = $v->TANGGAL;
                    $idata = [
                        'USERID' => $v->USERID,
                        'CHECKTIME' => $v->CHECKTIME,
                        'CHECKTYPE' => $v->CHECKTYPE,
                        'VERIFYCODE' => $v->VERIFYCODE,
                        'status' => 0,
                        'transferdate' => date('Y-m-d H:i:s'),
                        'istransfer' => $l
                    ];
                }
                echo json_encode($idata);
                echo "<hr>";
                $this->db->insert('t_attendance_temp', $idata);
                if ($this->db->affected_rows() > 0) {
                    $this->attdb->query("UPDATE CHECKINOUT SET VERIFYCODE=1 WHERE USERID=$_USERID AND CHECKTYPE='O' AND YEAR(CHECKTIME)=$_TAHUN AND MONTH(CHECKTIME)=$_BULAN AND DAY(CHECKTIME)=$_TANGGAL");
                }
                echo 'transfer OUT berhasil';
            }
        } else {
            echo '<br>=> tidak ada data OUT<br>';
        }
    }

    function tescmd()
    {
        echo "test " . date('Y-m-d H:i:s');
    }

    function tsOK()
    {
        $this->transferin();
        $this->transferout();
        echo "test";
        $cmd = 'cmd /C "php -f D:/xampp/htdocs/maisys/index.php Attdb transferin" > D:/tscmd.txt';
        $powershell = getenv('WINDIR') . "\SysWOW64\cmd.exe $cmd";
        $output = exec($powershell);
        echo $output;
    }

    function ts()
    {
        $Y = DATE('Y');
        $M = date('m');
        $D = date('d');

        $data = $this->attdb->query("SELECT DISTINCT USERID  FROM CHECKINOUT WHERE CHECKTYPE='O' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D");

        $i = 0;
        $l = count($data->result());

        echo $l . "<hr>";
        if ((int) $l > 0) {
            while ($l > $i) {
                $datasatu = $this->attdb->query("SELECT TOP 1 *  FROM CHECKINOUTVIEW WHERE CHECKTYPE='O' AND VERIFYCODE=0 AND YEAR(CHECKTIME)=$Y AND MONTH(CHECKTIME)=$M AND DAY(CHECKTIME)=$D ORDER BY CHECKTIME DESC")->result();
                if (count($datasatu) <= 0) {
                    echo "no data ";
                    return;
                }
                if ((int) $i >= (int) $l) {
                    echo "break <hr><hr>";
                    break;
                }
                $i += 1;
                echo json_encode($data);
                echo "<hr>";
                foreach ($datasatu as $k => $v) {
                    $_USERID = $v->USERID;
                    $_CHECKTIME = $v->CHECKTIME;
                    $_TAHUN = $v->TAHUN;
                    $_BULAN = $v->BULAN;
                    $_TANGGAL = $v->TANGGAL;
                    $idata = [
                        'USERID' => $v->USERID,
                        'CHECKTIME' => $v->CHECKTIME,
                        'CHECKTYPE' => $v->CHECKTYPE,
                        'VERIFYCODE' => $v->VERIFYCODE,
                        'status' => 0,
                        'transferdate' => date('Y-m-d H:i:s'),
                        'istransfer' => $l
                    ];
                }
                echo json_encode($idata);
                echo "<hr>";
                $this->db->insert('t_attendance_temp_copy', $idata);
                if ($this->db->affected_rows() > 0) {
                    $this->attdb->query("UPDATE CHECKINOUT SET VERIFYCODE=1 WHERE USERID=$_USERID AND CHECKTYPE='O' AND YEAR(CHECKTIME)=$_TAHUN AND MONTH(CHECKTIME)=$_BULAN AND DAY(CHECKTIME)=$_TANGGAL");
                }
            }
        } else {
            echo 'tidak ada data';
        }
    }

    function menutransfer()
    {
        $month = date('m');
        $monthname = '';
        if ($month == 1) {
            $monthname = 'Januari';
        } else if ($month == 2) {
            $monthname = 'Februari';
        } else if ($month == 3) {
            $monthname = 'Maret';
        } else if ($month == 4) {
            $monthname = 'April';
        } else if ($month == 5) {
            $monthname = 'Mei';
        } else if ($month == 6) {
            $monthname = 'Juni';
        } else if ($month == 7) {
            $monthname = 'Juli';
        } else if ($month == 8) {
            $monthname = 'Agustus';
        } else if ($month == 9) {
            $monthname = 'September';
        } else if ($month == 10) {
            $monthname = 'Oktober';
        } else if ($month == 11) {
            $monthname = 'November';
        } else if ($month == 12) {
            $monthname = 'Desember';
        }
        $vars = [
            'rview' => "transfer/formtransfer",
            'bulan' => $month,
            'namabulan' => $monthname
        ];

        $this->load->view("home/homew3", $vars);
    }

    //improvement

    function add()
    {
        $userid = $this->input->post('userid', TRUE);
        $tanggalkerja = $this->input->post("tanggalkerja", TRUE);
        $jammasuk = $this->input->post("jammasuk", TRUE);
        $jampulang = $this->input->post("jampulang", TRUE);
        $statusabsensi = $this->input->post("statusabsensi", TRUE);
        $keterangan = $this->input->post("keterangan", TRUE);
        $idata = [
            'UserID' => $userid,
            'Date' => $tanggalkerja,
            'CheckInTime' => $jammasuk,
            'CheckOutTime' => $jampulang,
            'StatusFin' => $statusabsensi,
            'KeteranganOT' => $keterangan
        ];
        $this->db->insert('t_attendance', $idata);
        $checktimetemp = date_create($tanggalkerja . ' ' .  $jammasuk);
        $checktimetempok = date_format($checktimetemp, 'Y-m-d H:i:s');
        $checktimetempout = date_create($tanggalkerja . ' ' .  $jampulang);
        $checktimetempoutok = date_format($checktimetempout, 'Y-m-d H:i:s');

        $this->db->insert('t_attendance_temp', [
            'USERID' => $userid,
            'CHECKTIME' => $checktimetempok,
            'CHECKTYPE' => 'I'
        ]);
        $this->db->insert('t_attendance_temp', [
            'USERID' => $userid,
            'CHECKTIME' => $checktimetempoutok,
            'CHECKTYPE' => 'O'
        ]);
        if ($tanggalkerja == null) {
            echo 'isi tanggal kerja';
            return;
        }
        if ($this->db->affected_rows() > 0) {
            echo json_encode(['Pesan' => 'Insert Data Berhasil']);
            // return redirect($_SERVER['HTTP_REFERER']);
        } else {
            echo 'please try again';
        }
    }

    function cekabsensi()
    {
        $userid = $this->input->get('u', TRUE);
        $dari = $this->input->get('t', TRUE);
        $sampai = date("Y-m-t", strtotime($dari));
        $data = $this->db->query("call attpegawai('$userid','$dari','$sampai')");
        $table = "<table border='1' class='w3-table-all' style='height:100px;'>
        <thead>
        <th>User Id</th>
        <th>Tanggal Kerja</th>
        <th>Jam (Att)</th>
        <th>Masuk (T)</th>
        <th>Pulang (T)</th>
        </thead>
        <tbody>";
        $tr = "";
        foreach ($data->result() as $k => $v) {
            $tr .= "<tr class='item'>
            <td>$v->USERID</td>
            <td>$v->tanggalkerja</td>
            <td>$v->kerja_att</td>
            <td>$v->masuk_kerja_temp</td>
            <td>$v->pulang_kerja_temp</td></trt>";
        }
        $rtable = $table . $tr . "</tbody><th>Periode</th></th><th colspan='4'>$dari s/d $sampai </th><tfoot></tfoot></table>";
        echo $rtable;
    }
}
