<?php
use DB;
use Hash;
use Session;
use Firebase\JWT\JWT;
use Fast\Eloquent\Model;
use Fast\Http\Exceptions\AppException;
use Fast\Contracts\Auth\Authentication;