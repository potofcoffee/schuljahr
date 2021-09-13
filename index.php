<?php

    require_once ('vendor/autoload.php');

    $year = date('Y');
    $h1 = json_decode(file_get_contents('https://ferien-api.de/api/v1/holidays/BW/'.$year), true);
    foreach ($h1 as $holiday) {
        if ($holiday['name'] == 'sommerferien') $yearStart = (new \Carbon\Carbon($holiday['end']))->addDay(1);
    }
    $h2 = json_decode(file_get_contents('https://ferien-api.de/api/v1/holidays/BW/'.($year+1)), true);
    foreach ($h2 as $holiday) {
        if ($holiday['name'] == 'sommerferien') $yearEnd = (new \Carbon\Carbon($holiday['start']))->subDay(1);
    }

?><!DOCTYPE html>
<html lang="de">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css" integrity="sha512-rxThY3LYIfYsVCWPCW9dB0k+e3RZB39f23ylUYTEuZMDrN/vRqLdaCBo/FbvVT6uC2r0ObfPzotsfKF9Qc5W5g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container p-3">
        <h1>Übersicht über das Schuljahr anlegen</h1>
        <form method="post" action="result.php">
            <div class="form-group">
                <label for="course">Bezeichnung der Klasse/des Kurses</label>
                <input type="text" class="form-control" name="course" value="" placeholder="z.B. Klasse 3a"/>
            </div>
            <div class="form-group">
                <label for="yearStart">Beginn des Schuljahres</label>
                <input type="text" class="form-control date" name="yearStart" value="<?= $yearStart->format('d.m.Y') ?>" placeholder="TT.MM.JJJJ"/>
            </div>
            <div class="form-group">
                <label for="yearEnd">Ende des Schuljahres</label>
                <input type="text" class="form-control date" name="yearEnd" value="<?= $yearEnd->format('d.m.Y') ?>"  placeholder="TT.MM.JJJJ"/>
            </div>
            <div class="form-group">
                <label for="times">Stundenplan</label>
                <textarea class="form-control" name="times" placeholder="Eine Angabe pro Zeile, z.B. Di 10:30 für Dienstag, 10:30 Uhr"></textarea>
            </div>
            <div class="form-group">
                <label for="holidays">Ferien</label>
                <textarea class="form-control" name="holidays"><?= file_get_contents('ferien.yaml') ?></textarea>
            </div>
            <div class="form-group">
                <label for="filetype">Dateityp</label>
                <select name="filetype" class="form-control">
                    <option value="Xlsx" selected>Microsoft Excel Arbeitsmappe (.xlsx)</option>
                    <option value="Xls">Microsoft Excel 97-2003 Arbeitsmappe (.xls)</option>
                    <option value="Ods">Open Document Format (.ods)</option>
                </select>
            </div>
            <hr />
            <input type="submit" class="btn btn-primary" value="Erstellen" />
        </form>
        <div class="mt-5 pt-5 text-small"><small>
            Schuljahr &copy; 2021 Christoph Fischer. Veröffentlicht als Open Source:
            <a href="https://github.com/potofcoffee/schuljahr" target="_blank">GitHub</a> &middot;
            <a href="https://raw.githubusercontent.com/potofcoffee/schuljahr/master/LICENSE" target="_blank">Lizenz</a>
            </small>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.de.min.js" integrity="sha512-3V4cUR2MLZNeqi+4bPuXnotN7VESQC2ynlNH/fUljXZiQk1BGowTqO5O2gElABNMIXzzpYg5d8DxNoXKlM210w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $('.date').datepicker({
            format: 'dd.mm.yyyy',
            language: 'de',
        });
    </script>
</body>
</html>
