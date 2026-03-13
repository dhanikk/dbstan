<?php

namespace Itpathsolutions\DBStan\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Itpathsolutions\DBStan\DBStanAnalyzer;

class DBStanController extends Controller
{
    public function index()
    {
        $analyzer = new DBStanAnalyzer();
        $preflightError = $analyzer->getPreflightError();

        $groupedIssues = $preflightError === null
            ? $analyzer->analyze()
            : [
                'structure' => [],
                'integrity' => [],
                'performance' => [],
                'architecture' => [],
            ];

        return view('dbstan::dbstan_issue_list', compact('groupedIssues', 'preflightError'));
    }
}
