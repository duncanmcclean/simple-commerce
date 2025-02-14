<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use Statamic\Facades\Site;
use Statamic\Tags\Concerns\GetsQueryResults;
use Statamic\Tags\Concerns\OutputsItems;
use Statamic\Tags\Concerns\QueriesConditions;
use Statamic\Tags\Concerns\QueriesOrderBys;
use Statamic\Tags\Concerns\QueriesScopes;
use Statamic\Tags\Tags;

class Orders extends Tags
{
    use GetsQueryResults, OutputsItems, QueriesConditions, QueriesOrderBys, QueriesScopes;

    public function index()
    {
        $query = Order::query();

        $this->querySite($query);
        $this->queryConditions($query);
        $this->queryOrderBys($query);
        $this->queryScopes($query);

        $results = $this->results($query);

        return $this->output($results);
    }

    protected function querySite($query)
    {
        $site = $this->params->get(['site', 'locale'], Site::current()->handle());

        if ($site === '*' || ! Site::hasMultiple()) {
            return;
        }

        return $query->where('site', $site);
    }
}
