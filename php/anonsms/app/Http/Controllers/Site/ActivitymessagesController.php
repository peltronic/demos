<?php
namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;

use App\Http\Controllers\SiteController;
use App\Libs\DatatableUtils\TableContainer;
use App\Models\Conversation;
use App\Models\Activitymessage;

class ActivitymessagesController extends SiteController
{
    public function __construct()
    {
        parent::__construct();

        $this->_assetMgr::registerJsLibs([
          '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js',
          '/js/app/common/libs/nlFormUtils.js',
        ]);
        $this->_assetMgr::registerJsInlines([
            '/js/app/common/inline/initDataTables.js',
        ]);
        $this->_assetMgr::registerCssInlines([
            '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css',
            '/css/app/main/styles.css',
        ]);
    }
  
    // Uses PsgcLaravelPackages\DatatableUtils\TableContainer to setup DataTables. For example
    // of DataTables w/o using this package see Site/AccountsController
    public function index(Request $request)
    {
        $sessionUser = \Auth::user();

        $dtC =  new TableContainer('activitymessages');
        $dtC->addColumn(
            'from',
            'From',
            function($r) { return $r->sender->renderName(); }
        );
        $dtC->addColumn(
            'subject',
            'Subject',
            function($r) { return $r->subject; }
        );
        $dtC->addColumn(
            'created_at', // example that does not use callback, defaults to raw column value
            'Date Sent'
        );

        if (!\Request::ajax()) {

            $this->_php2jsVars['datatables'] = [
                'activitymessages'=>[
                    'colconfig'=>$dtC->columnConfig(),
                ],
            ];
            \View::share('g_php2jsVars',$this->_php2jsVars);

            return \View::make('site.activitymessages.index', [
                'resource_key'=>'activitymessages',
            ]);

        } else {

            $paging = ['offset'=>$request->input('start',0),'length'=>$request->input('length',10)];
            $filters =  [ 'receiver' => ['type'=>'users','id'=>$sessionUser->id] ];
            $search = $request->input('search',[]);

            $sorting = null;
            /*
            $sorting = [
                'value' => $request->columns[$request->order[0]['column']]['name'],
                'direction' => $request->order[0]['dir']
            ];
             */

            $recordsTotal    = Activitymessage::collector()->getCount();
            $recordsFiltered = Activitymessage::collector()->getCount($filters,$search);
            $records         = Activitymessage::collector()->getList($filters,$search,$paging,$sorting);

            // Apply any post-adaptors (decorators)...  
            $options = $request->input('options',[]); 
            $records = $dtC->renderColumnVals($records); // set rendering for special fields such as links, FKs, etc

            $response = ['is_ok'=>1, 'records'=>$records, 'recordsTotal'=>$recordsTotal,'recordsFiltered'=>$recordsFiltered];
            return \Response::json($response);

        }
    }

    public function store(Request $request)
    {
        $conversation = Conversation::findOrFail($request->conversation_id);
        $conversation->updated_at = date('Y-m-d H:i:s');
        $conversation->save();

        $obj = Activitymessage::create($request->all());

        if (!\Request::ajax()) {
            return redirect( route('site.chat.show',$conversation->guid) );
        } else {
            return \Response::json(['obj'=>$obj]);
        }
    }

    public function show(Request $request, $slug)
    {
        $obj = Activitymessage::where('slug',$slug)->firstOrFail();
        if (!\Request::ajax()) {
            return \View::make('site.activitymessages.show', [ 'obj'=>$obj ]);
        } else {
            return \Response::json([
                'html' => \View::make($request->partial, [ 'obj'=>$obj ])->render(),
            ]);
        }
    }

}
