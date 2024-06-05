<?php

function isLogin()
{
    $pdo = $GLOBALS['pdo'];
    $session_login = $GLOBALS['session_login'];

    $cek_user = "SELECT id FROM `admin` WHERE session_login = ? AND session_login IS NOT NULL AND deleted_at IS NULL";
    $cek_user = $pdo->prepare($cek_user);
    $cek_user->execute([ $session_login ]);
    $pdo = null;

    return ($session_login != '' && $cek_user->rowCount() > 0);
}

function fetch_user()
{
    $pdo = $GLOBALS['pdo'];
    $session_login = $GLOBALS['session_login'];

    $cek_user = "SELECT nama, email, last_login FROM `admin` WHERE session_login = ? AND session_login IS NOT NULL AND deleted_at IS NULL";
    $cek_user = $pdo->prepare($cek_user);
    $cek_user->execute([ $session_login ]);
    $fetch_user = $cek_user->fetch(PDO::FETCH_OBJ);
    
    return $fetch_user;
}

function tglIndo($tgl)
{
	$strtime = strtotime($tgl);
	$hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu', 'Minggu'];
	$bulan = [
		'Januari',
		'Februari',
		'Maret', 
		'April', 
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	];

	$hari	= $hari[date('N', $strtime) - 1];
	$bulan	= $bulan[date('n', $strtime) - 1];
	$tgl 	= date('d', $strtime);
	$tahun 	= date('Y', $strtime);

	return $hari.', '.$tgl.' '.$bulan.' '.$tahun.' '.date('H:i', $strtime);
}

function pagination($page, $total_post, $limit, $permalink = '', $extension = '') 
{
    $total_page = ceil($total_post / $limit);  
    
    
    if (ceil($page + 1) <= $total_page)
        $next_page = '<li class="page-item"><a href="'.$permalink.ceil($page + 1).$extension.'" class="page-link">&raquo;</a></li>';
    else
        $next_page = '';


    if (ceil($page - 1) >= 1)
        $prev_page = '<li class="page-item"><a href="'.$permalink.ceil($page - 1).$extension.'" class="page-link">&laquo;</a></li>';
    else
        $prev_page = '';



    if ($total_page > 5 && $page <= ceil($total_page)) 
    {
        $first_page = ceil($page-2);

        if ($first_page < 1) 
            $first_page = 1;
        else if ($page >= ceil($total_page - 2))
            $first_page = ceil($total_page - 4);
    }
    else $first_page = 1;


    if ($total_page > 5 && $page <= ceil($total_page-3)) 
    {
        if ($first_page <= 2)
            $end_page = ceil($first_page+4);
        else
            $end_page = ceil(($page-1)+3);
    }
    else $end_page = $total_page;


    $pagination = '';
    if ($page <= $total_page && $page >= 1) 
    {
        $pagination .= $prev_page;

        for ($i = $first_page; $i <= $end_page; $i++) 
        {
            if ($i == $page) 
            {
                if ($total_page == 1)
                    $pagination .= '';
                else
                    $pagination .= '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
            }
            else
                $pagination .= '<li class="page-item"><a class="page-link" href="'.$permalink.$i.$extension.'">'.$i.'</a></li>';
        }

        $pagination .= $next_page;
    }

    if ($total_page > 1)
        return '
            <nav>
                <ul class="pagination">
                    '.$pagination.'
                </ul>
            </nav>';


    return null;
}

function cekRasioPersegi($file_path) {
    
    list($width, $height) = getimagesize($file_path);

    return ($width == $height);
}

?>