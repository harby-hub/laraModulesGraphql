<?php namespace Modules\Atom\GraphQL\Validators;

use Illuminate\Database\Schema\Builder;

use Illuminate\Validation\{Rule,Rules\Enum,Rules\Password};

use Illuminate\Support\{Str,Carbon};

trait inputs {

    public function Email( array $Arguments = [ ] ) : array {
        return [ 'email:rfc' , ...$Arguments ] ;
    }

    public function EmailExists( array $Arguments = [ 'required_without:phone' ] ) : array {
        return $this -> Email ( [ 'EmailProvidersExists' , ...$Arguments ] ) ;
    }

    public function EmailUnique( array $Arguments = [ 'required_without:phone' ] ) : array {
        return $this -> Email ( [ 'EmailProvidersUnique' , ...$Arguments ] ) ;
    }

    public function Password( array $Arguments = [ ] ) : array {
        return $this -> Text ( [ Password::min( 8 ) -> mixedCase( ) -> numbers( ) , ... $Arguments ] ) ;
    }

    public function Phone( string $nullable = 'nullable' , string $type = 'unique' , array $Arguments = [ ] ) : array {
        return $this -> Text ( [ $this -> Database( \Modules\Atom\Models\Contact::class , $type , 'value' , $nullable , $Arguments ) -> where( 'type' , 'phone' ) ] ) ;
    }

    public function Database( string $class , string $type = 'exists' , string $column = 'NULL' ) {
        return Rule::$type( $class , $column ) ;
    }

    public function is_Boolean( string $nullable = 'nullable' , array $Arguments = [ ] ) : array {
        return [ Rule::in( [ true , false , 0 , 1 , '0' , '1' , "true" , "false" ] ) , $nullable , ...$Arguments ] ;
    }

    public function firebase_id( array $Arguments = [ ] ) : array {
        return $this -> Text ( $Arguments + [ 'nullable' ] , 15 ) ;
    }

    public function Text( array $Arguments = [ ] , string $max = null ) : array {
        return [ 'string' , 'max:' . ( $max ?? Builder::$defaultStringLength ) , ...$Arguments ] ;
    }

    public function DateTime( string $nullable = 'nullable' , array $Arguments = [ ] , string $format = Carbon::DEFAULT_TO_STRING_FORMAT ) : array {
        return [ $nullable , 'date' , ...$Arguments , 'date_format:' . $format ];
    }

    public function DateFromTo( string $name = 'created_at' , array $Arguments = [ ] , string $format = Carbon::DEFAULT_TO_STRING_FORMAT ) : array {
        return [ 
            $name . '_from' => $this -> DateTime( 'nullable' , $Arguments , $format ) ,
            $name . '_to'   => $this -> DateTime( 'nullable' , $Arguments , $format ) 
        ] ;
    }

    public function Enums( string $class , string $required = 'required' , array $Arguments = [ ] ) : array {
        return [ $required , new Enum( $class ) , ...$Arguments ] ;
    }

    public function ArrayBase( string $nullable = 'nullable' , array $Arguments = [ ] , array $Attrs = [ ] ) : array {
        $type = 'array' ;
        if ( ! empty( $Attrs ) ) $type .= ':' . implode( ',' , $Attrs ) ;
        return [ $nullable  , $type , ...$Arguments ] ;
    }

    public function ArrayOf( string $name = 'id' , array $Arguments = [ ] ) : array {
        return [ $name => $this -> ArrayBase ( ) , $name . '.*' => $Arguments ] ;
    }

    public function Numeric( array $Arguments = [ ] ) : array {
        return [ 'numeric' , ...$Arguments ] ;
    }

    public function FloatNum( string $nullable = 'nullable' , array $Arguments = [ ] , int $nbMaxDecimals = 4 , int $max = 20 ) : array {
        $num = Str::substrReplace( str_repeat( '9' , $max ) , '.' , - $nbMaxDecimals , 0 );
        return $this -> Numeric( [ $nullable , 'between:-' . $num . ',' . $num , ...$Arguments ] );
    }

    public function latitude( array $Arguments = [ ] ) : array {
        return $this -> Numeric( [ 'between:-90,90' , ...$Arguments ] ) ;
    }

    public function longitude( array $Arguments = [ ] ) : array {
        return $this -> Numeric( [ 'between:-180,180' , ...$Arguments ] ) ;
    }

    public function AvailableAndActive( ) : array { return [
        'active' => $this -> is_Boolean ( ) ,
    ]; }

    public function Image( string $nullable = 'nullable' , int $max_size = 1024 , string $mimes = 'jpg,jpeg,png' , array $Arguments = [ ] ) : array {
        return [ 'file' , $nullable , 'mimes:' . $mimes , 'max:' . $max_size , ...$Arguments ];
    }

}