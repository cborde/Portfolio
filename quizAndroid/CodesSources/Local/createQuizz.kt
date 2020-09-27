package com.example.cocoquizz.Local

import android.os.Bundle
import android.view.View
import android.widget.EditText
import android.widget.Toast

import com.example.cocoquizz.R

import java.util.ArrayList
import java.util.TreeMap

import androidx.appcompat.app.AppCompatActivity

class createQuizz : AppCompatActivity() {

    private var theme = ""
    private var nbQuestions = 0
    private var nbQuestionsTotal = 0
    lateinit var dataBase: QuestionsDataBase
    internal var theme_id = 0

    lateinit var questions: Map<*, *>
    lateinit var reponses: Map<*, *>

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.create_quizz)
        dataBase = QuestionsDataBase(this)
    }

    /*
     *
     *
     * CREATE QUESTIONS
     *
     *
     *
     */

    /*
     * This function get the theme and the number of question of the user will write
     */

    fun createQuestions(v: View) {
        val themeText = findViewById(R.id.editTextTheme) as EditText

        val nbQuestionsText = findViewById(R.id.editTextNbQ) as EditText

        /*
         * Get Theme of the user wrote
         */

        theme = themeText.getText().toString().replace("\"", "\'")

        var themes = dataBase.getAllTheme()
        themes.moveToFirst()

        while (!themes.isAfterLast) {
            val t = themes.getString(0)
            if (t == theme) {
                Toast.makeText(this, "Ce thème existe déjà", Toast.LENGTH_SHORT).show()
                return
            }
            themes.moveToNext()
        }

        if (theme.length == 0) {
            Toast.makeText(this, "Vous devez remplir le champ Thème", Toast.LENGTH_SHORT).show()
            return
        }

        addThemeBD() //Add in DB

        /*
         * Get the number of question of the user wrote
         */

        if (nbQuestionsText.getText().toString().length == 0) {
            Toast.makeText(
                this,
                "Vous devez remplir le champ Nombre de Question",
                Toast.LENGTH_SHORT
            ).show()
            return
        }

        nbQuestions = Integer.parseInt(nbQuestionsText.getText().toString())
        if (nbQuestions < 0) {
            Toast.makeText(
                this,
                "Le nombre de question que vous avez saisi n'est pas correct, Recommencez",
                Toast.LENGTH_SHORT
            ).show()
        }

        nbQuestionsTotal = nbQuestions

        Toast.makeText(
            this,
            "Le thème et le nombre de question ont bien étés ajoutées !",
            Toast.LENGTH_SHORT
        ).show()

        setContentView(R.layout.create_questions_quizz)
    }

    /*
     * Creation of questions and answers
     */
    fun addQuestion(v: View) {
        questions = TreeMap<String, String>()
        reponses = TreeMap<String, List<String>>()

        if (nbQuestions == 0) { //The number of question wrote by the user is reached
            setContentView(R.layout.validation_question_add)
        } else {
            val qst = findViewById(R.id.editTextQuestion) as EditText

            val rep1 = findViewById(R.id.editTextREP1) as EditText
            val rep2 = findViewById(R.id.editTextREP2) as EditText
            val rep3 = findViewById(R.id.editTextREP3) as EditText
            val rep4 = findViewById(R.id.editTextREP4) as EditText

            val reps = ArrayList<String>()
            reps.add(rep1.getText().toString())
            reps.add(rep2.getText().toString())
            reps.add(rep3.getText().toString())
            reps.add(rep4.getText().toString())

            val reponseOK = findViewById(R.id.editTextReponseCorrecte) as EditText

            /*
             * The field Question must be filled
             */
            if (qst.getText().toString().length == 0) {
                Toast.makeText(this, "Vous devez remplir le champ Question", Toast.LENGTH_SHORT)
                    .show()
                return
            }

            /*
             * The field Correct Answer must be filled
             */
            if (reponseOK.getText().toString().length == 0) {
                Toast.makeText(this, "Vous devez chosir la bonne réponse", Toast.LENGTH_SHORT)
                    .show()
                return
            }

            val repOK = Integer.parseInt(reponseOK.getText().toString())

            /*
             * The field Correct Answer must be a number between 1 and 4 included
             */
            if (repOK < 0 || repOK > 4) {
                Toast.makeText(
                    this,
                    "La bonne réponse doit être entre 1 et 4, recommencez",
                    Toast.LENGTH_SHORT
                ).show()
                return
            }

            /*
             * The field Correct Answer must be a number who the answer's field filled
             */
            if (reps.get(repOK).length == 0){
                Toast.makeText(this, "Veuillez choisir une réponse qui n'est pas vide", Toast.LENGTH_LONG).show()
                return
            }

            /*
             * The " are replaced by ' because in the DB, the " is special character
             */
            val id_question = addQuestionBD(qst.getText().toString().replace("\"", "\'"), repOK)

            for (s in reps) {
                if (s.length > 0) {
                    addReponsesBD(s, id_question)
                }
            }

            Toast.makeText(this, "La question à bien été ajoutée", Toast.LENGTH_SHORT).show()

            /*
             * The fields are emptied
             */
            qst.setText("")

            rep1.setText("")
            rep2.setText("")
            rep3.setText("")
            rep4.setText("")

            reponseOK.setText("")

            nbQuestions--
            if (nbQuestions == 0) {
                setContentView(R.layout.validation_question_add)
                Toast.makeText(this, "Le quizz à bien été enregisté !", Toast.LENGTH_SHORT).show()
            }
        }
    }

    /*
     *
     *
     *  FUNCTIONS DATABASE HANDLER
     *
     *
     */

    /*
     * Add one theme in DB
     */

    fun addThemeBD() {
        theme_id = dataBase.getNextId("THEME")
        val res = dataBase.creerTheme(theme_id, theme)
    }

    /*
     * Add one question in DB
     */

    fun addQuestionBD(label: String, reponseOk_id: Int): Int {
        val question_id = dataBase.getNextId("QUESTION")
        dataBase.creerQuestion(question_id, label, theme_id, reponseOk_id)
        return question_id
    }

    /*
     * Add one reponse in DB
     */

    fun addReponsesBD(label: String, idQuestion: Int) {
        val id_reponse = dataBase.getNextId("REPONSE")
        dataBase.creerReponse(id_reponse, label, idQuestion)
    }

    /*
     *
     *
     * MENU
     *
     *
     */

    fun returnMenu(v: View) {
        super.finish()
    }
}
