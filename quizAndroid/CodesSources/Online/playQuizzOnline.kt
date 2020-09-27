package com.example.cocoquizz.Online

import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.*

import com.example.cocoquizz.Objets.Question
import com.example.cocoquizz.Objets.Quizz
import com.example.cocoquizz.Objets.Reponse
import com.example.cocoquizz.R

import java.util.ArrayList
import java.util.concurrent.ExecutionException

import androidx.appcompat.app.AppCompatActivity
import com.example.cocoquizz.Local.QuestionsDataBase

class playQuizzOnline : AppCompatActivity() {

    private val url = "https://dept-info.univ-fcomte.fr/joomla/images/CR0700/Quizzs.xml"
    internal var jeu: Quizz? = null

    lateinit var questions_play: ArrayList<Question>

    internal var nbQuestions = 0
    internal var count_tour = 0
    internal var score = 0

    lateinit var dataBase: QuestionsDataBase

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        download()
    }

    /*
     * Dowload the XML document
     */

    fun download() {

        val quizz = DownloadXML().execute(url) as DownloadXML
        try {
            jeu = quizz.get()
            if (jeu != null) {
                choose_theme()
            } else {
            }
        } catch (e: InterruptedException) {
        } catch (e: ExecutionException) {
        }

    }

    /*
     * Display theme for the user can choose
     */
    fun choose_theme() {
        setContentView(R.layout.choose_theme)
        val themes = jeu!!.themes

        val themeAdapter = ArrayAdapter(
            this,
            R.layout.items,
            R.id.theme_name,
            themes
        )

        val themeList = ListView(this)
        setContentView(themeList)
        themeList.adapter = themeAdapter

        themeList.onItemClickListener =
            AdapterView.OnItemClickListener { adapterView, view, position, rowId -> play(position) }
    }

    /*
     * Play quizz
     */
    fun play(theme: Int) {
        setContentView(R.layout.play_quizz)

        val id_des_questions = ArrayList<Int>()

        questions_play = ArrayList()
        val reponses_play = ArrayList<Reponse>()

        for (q in jeu!!.questions) {

            if (q.id_theme == theme) {
                questions_play.add(q)
                id_des_questions.add(q.id_question)
            }
        }

        nbQuestions = questions_play.size

        val qstV = findViewById(R.id.textViewQuestion) as TextView
        val b1 = findViewById(R.id.buttonRep1) as Button
        val b2 = findViewById(R.id.buttonRep2) as Button
        val b3 = findViewById(R.id.buttonRep3) as Button
        val b4 = findViewById(R.id.buttonRep4) as Button

        b1.setText("")
        b1.setBackgroundColor(Color.parseColor("#CC0000"))
        b1.setEnabled(false)

        b2.setText("")
        b2.setBackgroundColor(Color.parseColor("#CC0000"))
        b2.setEnabled(false)

        b3.setText("")
        b3.setBackgroundColor(Color.parseColor("#CC0000"))
        b3.setEnabled(false)

        b4.setText("")
        b4.setBackgroundColor(Color.parseColor("#CC0000"))
        b4.setEnabled(false)

        qstV.setText(questions_play[0].texteQuestion)

        for (r in jeu!!.reponses) {
            if (r.id_question == questions_play[0].id_question) {
                reponses_play.add(r)
            }
        }

        var i = 0
        for (r in reponses_play) {
            when (i) {
                0 -> {
                    b1.setText(r.texteReponse)
                    b1.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b1.setEnabled(true)
                }
                1 -> {
                    b2.setText(r.texteReponse)
                    b2.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b2.setEnabled(true)
                }
                2 -> {
                    b3.setText(r.texteReponse)
                    b3.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b3.setEnabled(true)
                }
                3 -> {
                    b4.setText(r.texteReponse)
                    b4.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b4.setEnabled(true)
                }
            }
            i++
        }
        count_tour++
    }

    fun clicReponse1(v: View) {
        verif(1)
    }

    fun clicReponse2(v: View) {
        verif(2)
    }

    fun clicReponse3(v: View) {
        verif(3)
    }

    fun clicReponse4(v: View) {
        verif(4)
    }

    fun verif(reponseProposee: Int) {
        val scoreT = findViewById(R.id.textViewScore) as TextView

        var msg = ""

        if (reponseProposee == questions_play[count_tour - 1].reponseOk_id) {
            score += 2
            scoreT.setText("Score : $score")
            msg = "Bonne réponse, Bravo !"
        } else {
            score -= 1
            scoreT.setText("Score : $score")
            msg = "Mauvaise réponse ! "
        }

        Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()

        if (count_tour == questions_play.size) { //There is no more questions
            setContentView(R.layout.fin_jeu)
            Toast.makeText(this, msg, Toast.LENGTH_SHORT).show()
            val s = findViewById(R.id.textViewFinScore) as TextView
            s.setText("Votre score : $score")
        } else {
            afficher()
        }
    }

    fun afficher() {
        val qstV = findViewById(R.id.textViewQuestion) as TextView
        val b1 = findViewById(R.id.buttonRep1) as Button
        val b2 = findViewById(R.id.buttonRep2) as Button
        val b3 = findViewById(R.id.buttonRep3) as Button
        val b4 = findViewById(R.id.buttonRep4) as Button

        b1.setText("")
        b1.setBackgroundColor(Color.parseColor("#CC0000"))
        b1.setEnabled(false)

        b2.setText("")
        b2.setBackgroundColor(Color.parseColor("#CC0000"))
        b2.setEnabled(false)

        b3.setText("")
        b3.setBackgroundColor(Color.parseColor("#CC0000"))
        b3.setEnabled(false)

        b4.setText("")
        b4.setBackgroundColor(Color.parseColor("#CC0000"))
        b4.setEnabled(false)

        qstV.setText(questions_play[count_tour].texteQuestion)

        val reponses_play = ArrayList<Reponse>()

        for (r in jeu!!.reponses) {
            if (r.id_question == questions_play[count_tour].id_question) {
                reponses_play.add(r)
            }
        }

        var i = 0
        for (r in reponses_play) {
            when (i) {
                0 -> {
                    b1.setText(r.texteReponse)
                    b1.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b1.setEnabled(true)
                }
                1 -> {
                    b2.setText(r.texteReponse)
                    b2.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b2.setEnabled(true)
                }
                2 -> {
                    b3.setText(r.texteReponse)
                    b3.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b3.setEnabled(true)
                }
                3 -> {
                    b4.setText(r.texteReponse)
                    b4.setBackgroundColor(Color.parseColor("#F8EF31"))
                    b4.setEnabled(true)
                }
            }
            i++
        }
        count_tour++
    }

    fun addScore(v: View){
        setContentView(R.layout.add_score_bd)
        var textScore = findViewById(R.id.TextViewScoreRappel) as TextView
        textScore.setText("Score : $score")
    }

    fun addScoreMenu(v: View){
        var p = findViewById(R.id.editTextPseudo) as EditText
        dataBase = QuestionsDataBase(this)
        dataBase.addScore(score, p.getText().toString().replace("\"", "\'"))
        super.finish()
    }

    fun menu(v: View) {
        super.finish()
    }
}
