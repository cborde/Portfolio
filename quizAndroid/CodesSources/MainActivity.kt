package com.example.cocoquizz

import com.example.cocoquizz.Gestion.gestion
import com.example.cocoquizz.Local.createQuizz
import com.example.cocoquizz.Local.playQuizzLocal
import com.example.cocoquizz.Online.playQuizzOnline
import com.example.cocoquizz.Score.score
import androidx.appcompat.app.AppCompatActivity

import android.content.Intent
import android.os.Bundle
import android.view.View

import android.provider.AlarmClock.EXTRA_MESSAGE

class MainActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
    }

    /*
     * Play with the quizz online
     */
    fun play(v: View) {
        val play = Intent(this@MainActivity, playQuizzOnline::class.java)
        play.putExtra(EXTRA_MESSAGE, "PLAY QUIZZ ONLINE")
        startActivityForResult(play, 1)
    }

    /*
     * Create a new quizz local
     */
    fun createQuizz(v: View) {
        val createQ = Intent(this@MainActivity, createQuizz::class.java)
        createQ.putExtra(EXTRA_MESSAGE, "CREATE QUIZZ")
        startActivityForResult(createQ, 1)
    }

    /*
     * Play with the locals quizz
     */
    fun playQuizzLocal(v: View) {
        val play = Intent(this@MainActivity, playQuizzLocal::class.java)
        play.putExtra(EXTRA_MESSAGE, "PLAY LOCAL")
        startActivityForResult(play, 1)
    }

    /*
     * Quizz handler
     */

    fun gerer(v: View) {
        val gerer = Intent(this@MainActivity, gestion::class.java)
        gerer.putExtra(EXTRA_MESSAGE, "GERER")
        startActivityForResult(gerer, 1)
    }

    /*
     * See all scores
     */

    fun voirScore(v: View){
        val score = Intent(this@MainActivity, score::class.java)
        score.putExtra(EXTRA_MESSAGE, "SCORE")
        startActivityForResult(score, 1)
    }

    /*
     * Informations of the application
     */

    fun info(v: View) {
        setContentView(R.layout.infos)
    }

    /*
     * Return to the menu when the user is in the information page
     */

    fun menu(v: View) {
        setContentView(R.layout.activity_main)
    }
}

