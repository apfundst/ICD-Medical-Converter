<?

include_once('util.php');

if (!mysql_connect('localhost', 'jsalvo_icd910', '5+7TH-P*a.qM')) {
    preit("Failed to connect: " . mysql_error());
    die;
}
else {
    #preit("connected");
}

if (!mysql_select_db('jsalvo_icd9_icd10_converter')) {
    preit("Failed to select db: " . mysql_error());
    die;
}
else {
    #preit("db selected");
}

