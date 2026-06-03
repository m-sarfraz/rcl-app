<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerEditionTeam extends Model
{
    protected $table = 'player_edition_teams';

    protected $fillable = ['player_id','edition_id','team_id','jersey_number','transfer_from_team_id'];

    public function player()   { return $this->belongsTo(Player::class); }
    public function edition()  { return $this->belongsTo(Edition::class); }
    public function team()     { return $this->belongsTo(Team::class); }
}
