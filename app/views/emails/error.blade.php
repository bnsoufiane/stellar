@extends('emails.common')

@section('title')
    @include('emails.subject')
@stop

@section('emailbody')
    <table cellpadding="0" cellspacing="0" border="0" align="left" width="100%">
        <tr>
            <td valign="top">

                {{nl2br($trace->getMessage())}}<br/>

                <?php

                $rtn = "";
                $count = 0;
                foreach ($trace->getTrace() as $frame) {


                    $args = "";
                    if (isset($frame['args'])) {
                        $args = array();
                        foreach ($frame['args'] as $arg) {
                            if (is_string($arg)) {
                                $args[] = "'" . $arg . "'";
                            } elseif (is_array($arg)) {
                                $args[] = "Array";
                            } elseif (is_null($arg)) {
                                $args[] = 'NULL';
                            } elseif (is_bool($arg)) {
                                $args[] = ($arg) ? "true" : "false";
                            } elseif (is_object($arg)) {
                                $args[] = get_class($arg);
                            } elseif (is_resource($arg)) {
                                $args[] = get_resource_type($arg);
                            } else {
                                $args[] = $arg;
                            }
                        }
                        $args = join(", ", $args);
                    }
                    $current_file = "[internal function]";
                    if (isset($frame['file'])) {
                        $current_file = $frame['file'];
                    }
                    $current_line = "";
                    if (isset($frame['line'])) {
                        $current_line = $frame['line'];
                    }
                    $rtn .= sprintf("#%s %s(%s): %s(%s)"."<br/>",
                            $count,
                            $current_file,
                            $current_line,
                            $frame['function'],
                            $args);
                    $count++;
                }
                ?>
                {{{$rtn}}}



                <h2>PHP environment:</h2>
                <pre style="max-width:600px;">
$_SERVER = {{ print_r($_SERVER, true) }}

                    $_REQUEST = {{ print_r($_REQUEST, true) }}
                </pre>
            </td>
        </tr>
    </table>
@stop