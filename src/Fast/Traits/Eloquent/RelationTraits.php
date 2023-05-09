<?php
namespace Fast\Traits\Eloquent;

use Fast\Eloquent\Relationship\HasOneRelation;

trait RelationTraits {
	public function hasOne(string $model, string $localKey = '', string $remoteKey = ''): HasOneRelation {
		return new HasOneRelation($model, $localKey, $remoteKey, $this);
	}
}