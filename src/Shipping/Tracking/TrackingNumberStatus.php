<?php

namespace DuncanMcClean\SimpleCommerce\Shipping\Tracking;

enum TrackingNumberStatus: string {
    /**
     * The TrackingNumber has been created, but the package has not yet been
     * received.
     */
    case Created = 'created';

    /**
     * The package has been recieved by the shipping provider and is on route to
     * its destination.
     */
    case Shipped = 'shipped';

    /**
     * The package has been delivered to its destination.
     */
    case Delivered = 'delivered';
    
    /**
     * TODO: Should break this down into multiple statuses.
     * 
     * A catch all for all shipment errors. i.e. Lost package, damaged package, etc.
     */
    case Error = 'error';
}
