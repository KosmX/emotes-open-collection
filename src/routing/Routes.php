<?php

namespace routing;

enum Routes
{
    case SELF_SERVED;
    case NOT_FOUND;
    case NO_RESOURCE;
    case DEFAULT;
}